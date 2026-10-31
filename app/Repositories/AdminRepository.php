<?php

class AdminRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function branchCondition(
        ?int $branchId,
        string $column = 'branch_id'
    ): array {
        if ($branchId === null) {
            return ['', []];
        }

        return [" AND {$column} = :branch_id", [':branch_id' => $branchId]];
    }

    public function getTotalSales(string $from, string $to, ?int $branchId = null): float
    {
        $sql = "
            SELECT COALESCE(SUM(total), 0)
            FROM orders
            WHERE created_at >= :from
              AND created_at < :to
              AND status NOT IN ('cancelled', 'refunded')
        ";
        $params = [':from' => $from, ':to' => $to];

        if ($branchId !== null) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (float) $stmt->fetchColumn();
    }

    public function getOrderCount(string $from, string $to, ?int $branchId = null): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM orders
            WHERE created_at >= :from
              AND created_at < :to
              AND status NOT IN ('cancelled', 'refunded')
        ";
        $params = [':from' => $from, ':to' => $to];

        if ($branchId !== null) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getAverageOrderValue(string $from, string $to, ?int $branchId = null): float
    {
        $sql = "
            SELECT COALESCE(AVG(total), 0)
            FROM orders
            WHERE created_at >= :from
              AND created_at < :to
              AND status NOT IN ('cancelled', 'refunded')
        ";
        $params = [':from' => $from, ':to' => $to];

        if ($branchId !== null) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (float) $stmt->fetchColumn();
    }

    public function getCustomerCount(?int $branchId = null): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM users u
            WHERE u.is_active = 1
              AND u.role_id = (
                  SELECT id FROM roles WHERE name = 'customer' LIMIT 1
              )
        ";
        $params = [];

        if ($branchId !== null) {
            $sql .= " AND u.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getNewCustomerCount(string $from, string $to, ?int $branchId = null): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM users u
            WHERE u.is_active = 1
              AND u.role_id = (
                  SELECT id FROM roles WHERE name = 'customer' LIMIT 1
              )
              AND u.created_at >= :from
              AND u.created_at < :to
        ";
        $params = [':from' => $from, ':to' => $to];

        if ($branchId !== null) {
            $sql .= " AND u.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getProductCount(?int $branchId = null): int
    {
        if ($branchId === null) {
            $stmt = $this->db->query("
                SELECT COUNT(*)
                FROM items
                WHERE is_active = 1
            ");
            return (int) $stmt->fetchColumn();
        }

        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT i.id)
            FROM items i
            INNER JOIN inventory inv ON inv.item_id = i.id
            WHERE i.is_active = 1
              AND inv.branch_id = :branch_id
        ");
        $stmt->execute([':branch_id' => $branchId]);
        return (int) $stmt->fetchColumn();
    }

    public function getActiveBranchCount(?int $branchId = null): int
    {
        if ($branchId !== null) {
            return 1;
        }

        return (int) $this->db->query("
            SELECT COUNT(*)
            FROM branches
            WHERE is_active = 1
        ")->fetchColumn();
    }

    public function getReservationCount(string $from, string $to, ?int $branchId = null): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM restaurant_reservations
            WHERE reservation_date >= DATE(:from)
              AND reservation_date < DATE(:to)
              AND status <> 'cancelled'
        ";
        $params = [':from' => $from, ':to' => $to];

        if ($branchId !== null) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getPendingReservationCount(?int $branchId = null): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM restaurant_reservations
            WHERE status = 'pending'
        ";
        $params = [];

        if ($branchId !== null) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getCancelledOrderCount(string $from, string $to, ?int $branchId = null): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM orders
            WHERE created_at >= :from
              AND created_at < :to
              AND status IN ('cancelled', 'refunded')
        ";
        $params = [':from' => $from, ':to' => $to];

        if ($branchId !== null) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getLowStockCount(?int $branchId = null): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM inventory inv
            INNER JOIN items i ON i.id = inv.item_id
            WHERE i.is_active = 1
              AND inv.stock_quantity <= inv.min_stock_alert
        ";
        $params = [];

        if ($branchId !== null) {
            $sql .= " AND inv.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getSalesByDay(string $from, string $to, ?int $branchId = null): array
    {
        $sql = "
            SELECT
                DATE(created_at) AS sale_date,
                COALESCE(SUM(total), 0) AS total_sales,
                COUNT(*) AS order_count
            FROM orders
            WHERE created_at >= :from
              AND created_at < :to
              AND status NOT IN ('cancelled', 'refunded')
        ";
        $params = [':from' => $from, ':to' => $to];

        if ($branchId !== null) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $sql .= "
            GROUP BY DATE(created_at)
            ORDER BY sale_date ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesByCategory(string $from, string $to, ?int $branchId = null): array
    {
        $sql = "
            SELECT
                COALESCE(c.name, 'Sin categoría') AS category_name,
                COALESCE(SUM(oi.total_price), 0) AS total_sales,
                COALESCE(SUM(oi.quantity), 0) AS units
            FROM order_items oi
            INNER JOIN orders o ON o.id = oi.order_id
            INNER JOIN items i ON i.id = oi.item_id
            LEFT JOIN categories c ON c.id = i.category_id
            WHERE o.created_at >= :from
              AND o.created_at < :to
              AND o.status NOT IN ('cancelled', 'refunded')
        ";
        $params = [':from' => $from, ':to' => $to];

        if ($branchId !== null) {
            $sql .= " AND o.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $sql .= "
            GROUP BY c.id, c.name
            ORDER BY total_sales DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopProducts(string $from, string $to, ?int $branchId = null, int $limit = 8): array
    {
        $limit = max(1, min($limit, 20));

        $sql = "
            SELECT
                i.id,
                i.name,
                i.price,
                COALESCE(c.name, 'Sin categoría') AS category_name,
                COALESCE(SUM(oi.quantity), 0) AS units_sold,
                COALESCE(SUM(oi.total_price), 0) AS revenue,
                (
                    SELECT ii.image_path
                    FROM item_images ii
                    WHERE ii.item_id = i.id
                      AND ii.is_active = 1
                    ORDER BY ii.is_favorite DESC, ii.display_order ASC, ii.id ASC
                    LIMIT 1
                ) AS favorite_image
            FROM order_items oi
            INNER JOIN orders o ON o.id = oi.order_id
            INNER JOIN items i ON i.id = oi.item_id
            LEFT JOIN categories c ON c.id = i.category_id
            WHERE o.created_at >= :from
              AND o.created_at < :to
              AND o.status NOT IN ('cancelled', 'refunded')
        ";
        $params = [':from' => $from, ':to' => $to];

        if ($branchId !== null) {
            $sql .= " AND o.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $sql .= "
            GROUP BY i.id, i.name, i.price, c.name
            ORDER BY units_sold DESC, revenue DESC
            LIMIT {$limit}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentOrders(string $from, string $to, ?int $branchId = null, int $limit = 10): array
    {
        $limit = max(1, min($limit, 30));

        $sql = "
            SELECT
                o.id,
                o.order_number,
                o.status,
                o.order_type,
                o.total,
                o.created_at,
                COALESCE(
                    NULLIF(TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))), ''),
                    'Cliente general'
                ) AS customer_name,
                b.name AS branch_name
            FROM orders o
            LEFT JOIN users u ON u.id = o.customer_id
            INNER JOIN branches b ON b.id = o.branch_id
            WHERE o.created_at >= :from
              AND o.created_at < :to
        ";
        $params = [':from' => $from, ':to' => $to];

        if ($branchId !== null) {
            $sql .= " AND o.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $sql .= " ORDER BY o.created_at DESC LIMIT {$limit}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLowStock(string $from, string $to, ?int $branchId = null, int $limit = 8): array
    {
        $limit = max(1, min($limit, 20));

        $sql = "
            SELECT
                i.id,
                i.name,
                inv.stock_quantity,
                inv.min_stock_alert,
                b.name AS branch_name,
                COALESCE(c.name, 'Sin categoría') AS category_name
            FROM inventory inv
            INNER JOIN items i ON i.id = inv.item_id
            INNER JOIN branches b ON b.id = inv.branch_id
            LEFT JOIN categories c ON c.id = i.category_id
            WHERE i.is_active = 1
              AND inv.stock_quantity <= inv.min_stock_alert
        ";
        $params = [];

        if ($branchId !== null) {
            $sql .= " AND inv.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $sql .= "
            ORDER BY
                (inv.stock_quantity - inv.min_stock_alert) ASC,
                inv.stock_quantity ASC
            LIMIT {$limit}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesByStatus(string $from, string $to, ?int $branchId = null): array
    {
        $sql = "
            SELECT
                status,
                COUNT(*) AS order_count,
                COALESCE(SUM(total), 0) AS total_sales
            FROM orders
            WHERE created_at >= :from
              AND created_at < :to
        ";
        $params = [':from' => $from, ':to' => $to];

        if ($branchId !== null) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $sql .= " GROUP BY status ORDER BY order_count DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesByOrderType(string $from, string $to, ?int $branchId = null): array
    {
        $sql = "
            SELECT
                order_type,
                COUNT(*) AS order_count,
                COALESCE(SUM(total), 0) AS total_sales
            FROM orders
            WHERE created_at >= :from
              AND created_at < :to
              AND status NOT IN ('cancelled', 'refunded')
        ";
        $params = [':from' => $from, ':to' => $to];

        if ($branchId !== null) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $sql .= " GROUP BY order_type ORDER BY total_sales DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesByPayment(string $from, string $to, ?int $branchId = null): array
    {
        $sql = "
            SELECT
                COALESCE(pg.name, 'Sin método') AS payment_method,
                COUNT(*) AS payment_count,
                COALESCE(SUM(p.amount), 0) AS total_amount
            FROM payments p
            INNER JOIN orders o ON o.id = p.order_id
            LEFT JOIN payment_gateways pg ON pg.id = p.gateway_id
            WHERE p.created_at >= :from
              AND p.created_at < :to
              AND p.status = 'approved'
              AND o.status NOT IN ('cancelled', 'refunded')
        ";
        $params = [':from' => $from, ':to' => $to];

        if ($branchId !== null) {
            $sql .= " AND o.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $sql .= " GROUP BY pg.id, pg.name ORDER BY total_amount DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesByHour(string $from, string $to, ?int $branchId = null): array
    {
        $sql = "
            SELECT
                HOUR(created_at) AS sale_hour,
                COUNT(*) AS order_count,
                COALESCE(SUM(total), 0) AS total_sales
            FROM orders
            WHERE created_at >= :from
              AND created_at < :to
              AND status NOT IN ('cancelled', 'refunded')
        ";
        $params = [':from' => $from, ':to' => $to];

        if ($branchId !== null) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $sql .= " GROUP BY HOUR(created_at) ORDER BY sale_hour ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReservationStatus(string $from, string $to, ?int $branchId = null): array
    {
        $sql = "
            SELECT
                status,
                COUNT(*) AS reservation_count
            FROM restaurant_reservations
            WHERE reservation_date >= DATE(:from)
              AND reservation_date < DATE(:to)
        ";
        $params = [':from' => $from, ':to' => $to];

        if ($branchId !== null) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $sql .= " GROUP BY status ORDER BY reservation_count DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBranchPerformance(string $from, string $to): array
    {
        $sql = "
            SELECT
                b.id,
                b.name,
                b.code,
                b.is_active,
                COALESCE((
                    SELECT SUM(o.total)
                    FROM orders o
                    WHERE o.branch_id = b.id
                      AND o.created_at >= :sales_from
                      AND o.created_at < :sales_to
                      AND o.status NOT IN ('cancelled', 'refunded')
                ), 0) AS total_sales,
                COALESCE((
                    SELECT COUNT(*)
                    FROM orders o2
                    WHERE o2.branch_id = b.id
                      AND o2.created_at >= :orders_from
                      AND o2.created_at < :orders_to
                      AND o2.status NOT IN ('cancelled', 'refunded')
                ), 0) AS order_count,
                COALESCE((
                    SELECT COUNT(*)
                    FROM restaurant_reservations rr
                    WHERE rr.branch_id = b.id
                      AND rr.reservation_date >= DATE(:res_from)
                      AND rr.reservation_date < DATE(:res_to)
                      AND rr.status <> 'cancelled'
                ), 0) AS reservation_count
            FROM branches b
            WHERE b.is_active = 1
            ORDER BY total_sales DESC, order_count DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':sales_from' => $from,
            ':sales_to' => $to,
            ':orders_from' => $from,
            ':orders_to' => $to,
            ':res_from' => $from,
            ':res_to' => $to
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
