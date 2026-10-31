<?php

class AdminReservationRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Lista reservas para el panel administrativo.
     *
     * branchId = null => todas las sedes (SuperAdmin).
     */
    public function getReservations(
        ?int $branchId = null,
        string $from = '',
        string $to = '',
        string $status = '',
        string $search = ''
    ): array {
        $conditions = [];
        $params = [];

        $sql = "
            SELECT
                rr.id,
                rr.reservation_code,
                rr.branch_id,
                b.code AS branch_code,
                b.name AS branch_name,
                rr.customer_id,
                CONCAT_WS(
                    ' ',
                    c.first_name,
                    c.last_name
                ) AS customer_name,
                c.email AS customer_email,
                c.phone AS customer_phone,
                rr.reservation_date,
                rr.start_time,
                rr.end_time,
                rr.people_count,
                rr.status,
                rr.source,
                rr.notes,
                rr.created_at,
                rr.updated_at
            FROM restaurant_reservations rr
            INNER JOIN branches b
                ON b.id = rr.branch_id
            INNER JOIN customers c
                ON c.id = rr.customer_id
        ";

        if ($branchId !== null) {
            $conditions[] = 'rr.branch_id = :branch_id';
            $params[':branch_id'] = $branchId;
        }

        if ($from !== '') {
            $conditions[] = 'rr.reservation_date >= :from';
            $params[':from'] = $from;
        }

        if ($to !== '') {
            $conditions[] = 'rr.reservation_date <= :to';
            $params[':to'] = $to;
        }

        if ($status !== '') {
            $conditions[] = 'rr.status = :status';
            $params[':status'] = $status;
        }

        if ($search !== '') {
            $conditions[] = "
                (
                    rr.reservation_code LIKE :search_code
                    OR c.email LIKE :search_email
                    OR c.first_name LIKE :search_first_name
                    OR c.last_name LIKE :search_last_name
                    OR CONCAT_WS(' ', c.first_name, c.last_name) LIKE :search_name
                )
            ";

            $like = '%' . $search . '%';
            $params[':search_code'] = $like;
            $params[':search_email'] = $like;
            $params[':search_first_name'] = $like;
            $params[':search_last_name'] = $like;
            $params[':search_name'] = $like;
        }

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= "
            ORDER BY
                rr.reservation_date DESC,
                rr.start_time DESC,
                rr.id DESC
            LIMIT 500
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['id'] = (int) $row['id'];
            $row['branch_id'] = (int) $row['branch_id'];
            $row['customer_id'] = (int) $row['customer_id'];
            $row['people_count'] = (int) $row['people_count'];
            $row['start_time'] = substr((string) $row['start_time'], 0, 5);
            $row['end_time'] = substr((string) $row['end_time'], 0, 5);
        }
        unset($row);

        return $rows;
    }

    /**
     * Estadísticas rápidas para el módulo.
     */
    public function getStats(?int $branchId = null): array
    {
        $condition = '';
        $params = [];

        if ($branchId !== null) {
            $condition = ' AND rr.branch_id = :branch_id';
            $params[':branch_id'] = $branchId;
        }

        $sql = "
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN rr.status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN rr.status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
                SUM(CASE WHEN rr.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
                SUM(
                    CASE
                        WHEN rr.reservation_date = CURDATE()
                         AND rr.status IN ('pending', 'confirmed', 'checked_in')
                        THEN 1
                        ELSE 0
                    END
                ) AS today
            FROM restaurant_reservations rr
            WHERE 1 = 1
            {$condition}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int) ($stats['total'] ?? 0),
            'pending' => (int) ($stats['pending'] ?? 0),
            'confirmed' => (int) ($stats['confirmed'] ?? 0),
            'cancelled' => (int) ($stats['cancelled'] ?? 0),
            'today' => (int) ($stats['today'] ?? 0)
        ];
    }
}
