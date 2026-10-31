<?php

class CouponRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                c.*,
                CASE
                    WHEN c.is_active = 0 THEN 'inactive'
                    WHEN c.starts_at IS NOT NULL AND c.starts_at > NOW() THEN 'scheduled'
                    WHEN c.expires_at IS NOT NULL AND c.expires_at < NOW() THEN 'expired'
                    WHEN c.usage_limit IS NOT NULL AND c.usage_count >= c.usage_limit THEN 'exhausted'
                    ELSE 'active'
                END AS computed_status
            FROM coupons c
            ORDER BY c.created_at DESC, c.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM coupons
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM coupons
            WHERE code = :code
            LIMIT 1
        ");
        $stmt->execute(['code' => strtoupper(trim($code))]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function countUsesByCustomer(int $couponId, int $customerId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM coupon_redemptions
            WHERE coupon_id = :coupon_id
              AND customer_id = :customer_id
        ");
        $stmt->execute([
            'coupon_id' => $couponId,
            'customer_id' => $customerId
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO coupons (
                code,
                description,
                discount_type,
                discount_value,
                minimum_purchase,
                starts_at,
                expires_at,
                usage_limit,
                per_customer_limit,
                is_active,
                created_by
            ) VALUES (
                :code,
                :description,
                :discount_type,
                :discount_value,
                :minimum_purchase,
                :starts_at,
                :expires_at,
                :usage_limit,
                :per_customer_limit,
                :is_active,
                :created_by
            )
        ");
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE coupons
            SET
                code = :code,
                description = :description,
                discount_type = :discount_type,
                discount_value = :discount_value,
                minimum_purchase = :minimum_purchase,
                starts_at = :starts_at,
                expires_at = :expires_at,
                usage_limit = :usage_limit,
                per_customer_limit = :per_customer_limit,
                is_active = :is_active,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
            LIMIT 1
        ");

        $data['id'] = $id;
        $stmt->execute($data);

        return $stmt->rowCount() > 0;
    }

    public function setActive(int $id, int $isActive): bool
    {
        $stmt = $this->db->prepare("
            UPDATE coupons
            SET is_active = :is_active,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute([
            'id' => $id,
            'is_active' => $isActive
        ]);

        return $stmt->rowCount() > 0;
    }
}
