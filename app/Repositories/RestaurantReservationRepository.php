<?php
// app/Repositories/RestaurantReservationRepository.php

class RestaurantReservationRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Inicia una transacción.
     */
    public function beginTransaction(): void
    {
        $this->db->beginTransaction();
    }

    /**
     * Confirma la transacción.
     */
    public function commit(): void
    {
        $this->db->commit();
    }

    /**
     * Revierte la transacción.
     */
    public function rollBack(): void
    {
        $this->db->rollBack();
    }

    /**
     * Verifica si existe una transacción activa.
     */
    public function inTransaction(): bool
    {
        return $this->db->inTransaction();
    }

    /**
     * Bloquea las mesas seleccionadas dentro de la transacción actual.
     *
     * La transacción es controlada por el servicio.
     */
    public function lockTables(
        array $tableIds,
        int $branchId
    ): array {
        if (empty($tableIds)) {
            throw new InvalidArgumentException(
                'No se especificaron mesas para bloquear.'
            );
        }

        $placeholders = implode(
            ',',
            array_fill(0, count($tableIds), '?')
        );

        $sql = "
            SELECT
                id,
                branch_id,
                capacity,
                status,
                is_active
            FROM restaurant_tables
            WHERE id IN ($placeholders)
              AND branch_id = ?
              AND is_active = 1
              AND status = 'available'
            FOR UPDATE
        ";

        $params = $tableIds;
        $params[] = $branchId;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta conflictos de reservas para las mesas seleccionadas.
     */
    public function countTableConflicts(
        array $tableIds,
        string $date,
        string $startTime,
        string $endTime
    ): int {
        if (empty($tableIds)) {
            return 0;
        }

        $placeholders = implode(
            ',',
            array_fill(0, count($tableIds), '?')
        );

        $sql = "
            SELECT COUNT(*) AS total
            FROM reservation_tables rtt
            INNER JOIN restaurant_reservations rr
                ON rr.id = rtt.reservation_id
            WHERE rtt.table_id IN ($placeholders)
              AND rr.reservation_date = ?
              AND rr.status IN (
                  'pending',
                  'confirmed',
                  'checked_in'
              )
              AND rr.start_time < ?
              AND rr.end_time > ?
        ";

        $params = $tableIds;
        $params[] = $date;
        $params[] = $endTime;
        $params[] = $startTime;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Genera un código único para la reserva.
     */
    public function generateUniqueReservationCode(): string
    {
        do {
            $code = 'RSV-' . strtoupper(
                    substr(bin2hex(random_bytes(4)), 0, 6)
                );

            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM restaurant_reservations
                WHERE reservation_code = ?
            ");

            $stmt->execute([$code]);

            $exists = (int) $stmt->fetchColumn();

        } while ($exists > 0);

        return $code;
    }

    /**
     * Crea la reserva.
     */
    public function create(array $data): int
    {
        $sql = "
            INSERT INTO restaurant_reservations (
                reservation_code,
                branch_id,
                customer_id,
                reservation_date,
                start_time,
                end_time,
                people_count,
                status,
                source,
                notes,
                confirmed_at,
                created_at,
                updated_at
            )
            VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            $data['reservation_code'],
            $data['branch_id'],
            $data['customer_id'],
            $data['reservation_date'],
            $data['start_time'],
            $data['end_time'],
            $data['people_count'],
            $data['status'],
            $data['source'] ?? 'web',
            $data['notes'] ?? null,
            $data['confirmed_at'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Asigna las mesas a una reserva.
     */
    public function assignTables(
        int $reservationId,
        array $tableIds
    ): void {
        if (empty($tableIds)) {
            throw new InvalidArgumentException(
                'No hay mesas para asignar.'
            );
        }

        $stmt = $this->db->prepare("
            INSERT INTO reservation_tables (
                reservation_id,
                table_id,
                assigned_at
            )
            VALUES (?, ?, NOW())
        ");

        foreach ($tableIds as $tableId) {
            $stmt->execute([
                $reservationId,
                (int) $tableId
            ]);
        }
    }

    /**
     * Consulta pública de reservas.
     */
    public function searchPublicReservations(
        string $reservationCode = '',
        string $email = ''
    ): array {
        // Nunca permitir búsquedas públicas por un solo dato personal.
        $reservationCode = strtoupper(trim($reservationCode));
        $email = strtolower(trim($email));
        if (
            $reservationCode === '' ||
            $email === '' ||
            !preg_match('/^RSV-[A-Z0-9]{6}$/', $reservationCode) ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            return [];
        }

        $conditions = [];
        $params = [];

        $sql = "
            SELECT
                rr.id,
                rr.reservation_code,
                rr.reservation_date,
                rr.start_time,
                rr.end_time,
                rr.people_count,
                rr.status,
                rr.source,
                rr.notes,
                rr.created_at,

                b.id AS branch_id,
                b.code AS branch_code,
                b.name AS branch_name,
                b.phone AS branch_phone,
                b.email AS branch_email,
                b.address AS branch_address,

                c.id AS customer_id,
                c.first_name AS customer_first_name,
                c.last_name AS customer_last_name,
                CONCAT_WS(
                    ' ',
                    c.first_name,
                    c.last_name
                ) AS customer_name,
                c.email AS customer_email,
                c.phone AS customer_phone

            FROM restaurant_reservations rr

            INNER JOIN branches b
                ON b.id = rr.branch_id

            INNER JOIN customers c
                ON c.id = rr.customer_id
        ";

        if ($reservationCode !== '') {
            $conditions[] = "
                rr.reservation_code = :reservation_code
            ";

            $params[':reservation_code'] = $reservationCode;
        }

        if ($email !== '') {
            $conditions[] = "
                LOWER(TRIM(c.email)) =
                LOWER(TRIM(:email))
            ";

            $params[':email'] = $email;
        }

        if (!empty($conditions)) {
            $sql .= "
                WHERE " . implode(' AND ', $conditions);
        }

        $sql .= "
            ORDER BY
                rr.reservation_date DESC,
                rr.start_time DESC,
                rr.id DESC

            LIMIT 20
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($reservations as &$reservation) {
            $reservation['id'] =
                (int) $reservation['id'];

            $reservation['branch_id'] =
                (int) $reservation['branch_id'];

            $reservation['customer_id'] =
                (int) $reservation['customer_id'];

            $reservation['people_count'] =
                (int) $reservation['people_count'];

            if (!empty($reservation['start_time'])) {
                $reservation['start_time'] =
                    substr(
                        $reservation['start_time'],
                        0,
                        5
                    );
            }

            if (!empty($reservation['end_time'])) {
                $reservation['end_time'] =
                    substr(
                        $reservation['end_time'],
                        0,
                        5
                    );
            }
        }

        unset($reservation);

        return $reservations;
    }

    /**
     * Obtiene una reserva pública para cancelación.
     */
    public function findPublicReservationForCancellation(
        string $reservationCode,
        string $email
    ): ?array {
        $sql = "
            SELECT
                rr.id,
                rr.reservation_code,
                rr.branch_id,
                rr.customer_id,
                rr.reservation_date,
                rr.start_time,
                rr.end_time,
                rr.people_count,
                rr.status,

                b.name AS branch_name,
                b.phone AS branch_phone,
                b.email AS branch_email,

                c.first_name AS customer_first_name,
                c.last_name AS customer_last_name,
                c.email AS customer_email

            FROM restaurant_reservations rr

            INNER JOIN branches b
                ON b.id = rr.branch_id

            INNER JOIN customers c
                ON c.id = rr.customer_id

            WHERE rr.reservation_code = :reservation_code

              AND LOWER(TRIM(c.email))
                  = LOWER(TRIM(:email))

            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':reservation_code' => strtoupper(
                trim($reservationCode)
            ),
            ':email' => trim($email)
        ]);

        $reservation =
            $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            return null;
        }

        $reservation['id'] =
            (int) $reservation['id'];

        $reservation['branch_id'] =
            (int) $reservation['branch_id'];

        $reservation['customer_id'] =
            (int) $reservation['customer_id'];

        $reservation['people_count'] =
            (int) $reservation['people_count'];

        return $reservation;
    }

    /**
     * Obtiene la configuración de reservas de una sede.
     */
    public function getReservationSettings(
        int $branchId
    ): ?array {
        $stmt = $this->db->prepare("
            SELECT
                id,
                branch_id,
                is_enabled,
                min_people,
                max_people,
                slot_interval_minutes,
                reservation_duration_minutes,
                min_advance_minutes,
                max_advance_days,
                allow_same_day,
                allow_customer_cancellation,
                cancellation_limit_minutes,
                auto_confirm

            FROM reservation_settings

            WHERE branch_id = ?

            LIMIT 1
        ");

        $stmt->execute([
            $branchId
        ]);

        $settings =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return $settings ?: null;
    }

    /**
     * Cancela una reserva.
     *
     * Solamente actualiza el estado.
     * Las mesas no se eliminan de reservation_tables.
     */
    public function cancelReservation(
        int $reservationId,
        string $reason = 'Cancelación realizada por el cliente'
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE restaurant_reservations
            SET
                status = 'cancelled',
                cancellation_reason = ?,
                cancelled_at = NOW(),
                updated_at = NOW()

            WHERE id = ?

              AND status IN (
                  'pending',
                  'confirmed'
              )
        ");

        $stmt->execute([
            $reason,
            $reservationId
        ]);

        return $stmt->rowCount() > 0;
    }
}