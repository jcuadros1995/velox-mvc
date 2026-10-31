<?php
// app/Repositories/RestaurantTableRepository.php

class RestaurantTableRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * =========================================================
     * MESAS DISPONIBLES PARA RESERVAS
     * =========================================================
     */
    public function getAvailableTables(
        int $branchId,
        string $date,
        string $startTime,
        string $endTime
    ): array {

        $sql = "
            SELECT
                rt.id,
                rt.branch_id,
                rt.table_number,
                rt.name,
                rt.capacity,
                rt.location,
                rt.status
            FROM restaurant_tables rt
            WHERE rt.branch_id = :branch_id
              AND rt.is_active = 1
              AND rt.status = 'available'

              AND NOT EXISTS (
                  SELECT 1
                  FROM reservation_tables rtt
                  INNER JOIN restaurant_reservations rr
                      ON rr.id = rtt.reservation_id
                  WHERE rtt.table_id = rt.id
                    AND rr.reservation_date = :reservation_date
                    AND rr.status IN (
                        'pending',
                        'confirmed',
                        'checked_in'
                    )
                    AND rr.start_time < :end_time
                    AND rr.end_time > :start_time
              )

            ORDER BY
                rt.capacity ASC,
                rt.table_number ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':branch_id' =>
                $branchId,

            ':reservation_date' =>
                $date,

            ':start_time' =>
                $startTime,

            ':end_time' =>
                $endTime,
        ]);

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }


    /**
     * =========================================================
     * OBTENER MESAS DE UNA SEDE
     * =========================================================
     */
    public function getByBranch(
        int $branchId
    ): array {

        $stmt = $this->db->prepare("
            SELECT
                id,
                branch_id,
                table_number,
                name,
                capacity,
                location,
                status,
                is_active
            FROM restaurant_tables
            WHERE branch_id = :branch_id
            ORDER BY
                table_number ASC,
                id ASC
        ");

        $stmt->execute([
            ':branch_id' => $branchId
        ]);

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }


    /**
     * =========================================================
     * OBTENER UNA MESA
     * =========================================================
     */
    public function findById(
        int $tableId
    ): ?array {

        $stmt = $this->db->prepare("
            SELECT
                id,
                branch_id,
                table_number,
                name,
                capacity,
                location,
                status,
                is_active
            FROM restaurant_tables
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $tableId
        ]);

        $table =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );

        return $table ?: null;
    }


    /**
     * =========================================================
     * BUSCAR NÚMERO DE MESA
     * =========================================================
     */
    public function findByTableNumber(
        int $branchId,
        int $tableNumber,
        ?int $excludeId = null
    ): ?array {

        $sql = "
            SELECT
                id,
                branch_id,
                table_number,
                name,
                capacity,
                location,
                status,
                is_active
            FROM restaurant_tables
            WHERE branch_id = :branch_id
              AND table_number = :table_number
        ";

        $params = [
            ':branch_id' =>
                $branchId,

            ':table_number' =>
                $tableNumber
        ];

        if ($excludeId !== null) {

            $sql .= "
                AND id <> :exclude_id
            ";

            $params[':exclude_id'] =
                $excludeId;
        }

        $sql .= "
            LIMIT 1
        ";

        $stmt =
            $this->db->prepare($sql);

        $stmt->execute($params);

        $table =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );

        return $table ?: null;
    }


    /**
     * =========================================================
     * CREAR MESA
     * =========================================================
     */
    public function create(
        int $branchId,
        array $data
    ): int {

        $stmt = $this->db->prepare("
            INSERT INTO restaurant_tables (
                branch_id,
                table_number,
                name,
                capacity,
                location,
                status,
                is_active
            )
            VALUES (
                :branch_id,
                :table_number,
                :name,
                :capacity,
                :location,
                :status,
                :is_active
            )
        ");

        $stmt->execute([
            ':branch_id' =>
                $branchId,

            ':table_number' =>
                (int) $data['table_number'],

            ':name' =>
                $data['name'],

            ':capacity' =>
                (int) $data['capacity'],

            ':location' =>
                $data['location'],

            ':status' =>
                $data['status'],

            ':is_active' =>
                (int) $data['is_active']
        ]);

        return (int)
        $this->db->lastInsertId();
    }


    /**
     * =========================================================
     * ACTUALIZAR MESA
     * =========================================================
     */
    public function update(
        int $tableId,
        array $data
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE restaurant_tables
            SET
                table_number = :table_number,
                name = :name,
                capacity = :capacity,
                location = :location,
                status = :status,
                is_active = :is_active
            WHERE id = :id
        ");

        $stmt->execute([
            ':table_number' =>
                (int) $data['table_number'],

            ':name' =>
                $data['name'],

            ':capacity' =>
                (int) $data['capacity'],

            ':location' =>
                $data['location'],

            ':status' =>
                $data['status'],

            ':is_active' =>
                (int) $data['is_active'],

            ':id' =>
                $tableId
        ]);

        return $stmt->rowCount() > 0;
    }


    /**
     * =========================================================
     * DESACTIVAR MESA
     * =========================================================
     */
    public function deactivate(
        int $tableId
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE restaurant_tables
            SET
                is_active = 0
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $tableId
        ]);

        return $stmt->rowCount() > 0;
    }


    /**
     * =========================================================
     * CONTAR RESERVAS/HISTORIAL DE UNA MESA
     * =========================================================
     */
    public function countReservations(
        int $tableId
    ): int {

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM reservation_tables
            WHERE table_id = :table_id
        ");

        $stmt->execute([
            ':table_id' =>
                $tableId
        ]);

        return (int)
        $stmt->fetchColumn();
    }


    /**
     * =========================================================
     * ELIMINACIÓN FÍSICA
     *
     * Solo se utiliza cuando la mesa nunca
     * ha sido relacionada con una reserva.
     * =========================================================
     */
    public function delete(
        int $tableId
    ): bool {

        $stmt = $this->db->prepare("
            DELETE FROM restaurant_tables
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $tableId
        ]);

        return $stmt->rowCount() > 0;
    }


    /**
     * =========================================================
     * SIGUIENTE NÚMERO DE MESA
     * =========================================================
     */
    public function getNextTableNumber(
        int $branchId
    ): int {

        $stmt = $this->db->prepare("
            SELECT
                COALESCE(
                    MAX(table_number),
                    0
                ) + 1
            FROM restaurant_tables
            WHERE branch_id = :branch_id
        ");

        $stmt->execute([
            ':branch_id' =>
                $branchId
        ]);

        return max(
            1,
            (int) $stmt->fetchColumn()
        );
    }

    public function getReservationsByTable(int $tableId): array
    {
        $sql = "
        SELECT
            rr.id AS reservation_id,
            rr.reservation_code,
            rr.reservation_date,
            rr.start_time,
            rr.end_time,
            rr.people_count,
            rr.status
        FROM reservation_tables rtb
        INNER JOIN restaurant_reservations rr
            ON rr.id = rtb.reservation_id
        WHERE rtb.table_id = ?
        ORDER BY
            rr.reservation_date DESC,
            rr.start_time DESC
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tableId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}