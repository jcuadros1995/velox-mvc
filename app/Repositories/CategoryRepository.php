<?php
// app/Repositories/CategoryRepository.php

class CategoryRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todas las categorías con jerarquía,
     * cantidad de productos y estado de su página pública.
     */
    public function getAllForAdmin(): array
    {
        $sql = "
            SELECT
                c.id,
                c.parent_id,
                c.name,
                c.slug,
                c.catalog_type,
                c.description,
                c.is_active,
                c.created_at,

                p.name AS parent_name,

                (
                    SELECT COUNT(*)
                    FROM items i
                    WHERE i.category_id = c.id
                ) AS item_count,

                (
                    SELECT COUNT(*)
                    FROM categories child
                    WHERE child.parent_id = c.id
                ) AS children_count,

                cp.id AS category_page_id,
                cp.slug AS page_slug,
                cp.is_published AS page_is_published

            FROM categories c

            LEFT JOIN categories p
                ON p.id = c.parent_id

            LEFT JOIN category_pages cp
                ON cp.category_id = c.id

            ORDER BY
                CASE
                    WHEN c.parent_id IS NULL THEN 0
                    ELSE 1
                END,
                COALESCE(p.name, c.name) ASC,
                c.parent_id ASC,
                c.name ASC
        ";

        return $this->db
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Categorías disponibles para seleccionar como padre.
     */
    public function getParents(?int $excludeId = null): array
    {
        $sql = "
            SELECT
                id,
                parent_id,
                name,
                slug,
                is_active
            FROM categories
            WHERE is_active = 1
        ";

        $params = [];

        if ($excludeId !== null && $excludeId > 0) {
            $sql .= " AND id <> :exclude_id ";
            $params['exclude_id'] = $excludeId;
        }

        $sql .= "
            ORDER BY
                name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare("\n            SELECT\n                id,\n                parent_id,\n                name,\n                slug,\n                catalog_type,\n                description,\n                is_active,\n                created_at\n            FROM categories\n            WHERE id = :id\n            LIMIT 1\n        ");

        $stmt->execute(['id' => $id]);

        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        return $category ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("\n            INSERT INTO categories\n            (\n                parent_id,\n                name,\n                slug,\n                catalog_type,\n                description,\n                is_active\n            )\n            VALUES\n            (\n                :parent_id,\n                :name,\n                :slug,\n                :catalog_type,\n                :description,\n                :is_active\n            )\n        ");

        $stmt->execute([
            'parent_id' => $data['parent_id'],
            'name' => $data['name'],
            'slug' => $data['slug'],
            'catalog_type' => $data['catalog_type'],
            'description' => $data['description'],
            'is_active' => $data['is_active']
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare("\n            UPDATE categories\n            SET\n                parent_id = :parent_id,\n                name = :name,\n                slug = :slug,\n                catalog_type = :catalog_type,\n                description = :description,\n                is_active = :is_active\n            WHERE id = :id\n        ");

        return $stmt->execute([
            'id' => $id,
            'parent_id' => $data['parent_id'],
            'name' => $data['name'],
            'slug' => $data['slug'],
            'catalog_type' => $data['catalog_type'],
            'description' => $data['description'],
            'is_active' => $data['is_active']
        ]);
    }

    public function setActive(int $id, int $isActive): bool
    {
        $stmt = $this->db->prepare("\n            UPDATE categories\n            SET is_active = :is_active\n            WHERE id = :id\n        ");

        return $stmt->execute([
            'id' => $id,
            'is_active' => $isActive ? 1 : 0
        ]);
    }

    public function hasChildren(int $id): bool
    {
        $stmt = $this->db->prepare("\n            SELECT EXISTS(\n                SELECT 1\n                FROM categories\n                WHERE parent_id = :id\n            )\n        ");

        $stmt->execute(['id' => $id]);

        return (bool) $stmt->fetchColumn();
    }

    public function hasItems(int $id): bool
    {
        $stmt = $this->db->prepare("\n            SELECT EXISTS(\n                SELECT 1\n                FROM items\n                WHERE category_id = :id\n            )\n        ");

        $stmt->execute(['id' => $id]);

        return (bool) $stmt->fetchColumn();
    }

    public function getDescendantIds(int $id): array
    {
        if ($id <= 0) {
            return [];
        }

        $all = $this->db
            ->query("SELECT id, parent_id FROM categories")
            ->fetchAll(PDO::FETCH_ASSOC);

        $children = [];

        foreach ($all as $row) {
            $parentId = $row['parent_id'] !== null
                ? (int) $row['parent_id']
                : 0;

            $children[$parentId][] = (int) $row['id'];
        }

        $result = [];
        $queue = $children[$id] ?? [];

        while ($queue) {
            $childId = array_shift($queue);

            if (in_array($childId, $result, true)) {
                continue;
            }

            $result[] = $childId;

            foreach ($children[$childId] ?? [] as $grandChildId) {
                $queue[] = $grandChildId;
            }
        }

        return $result;
    }

    public function getPageByCategoryId(int $categoryId): ?array
    {
        $stmt = $this->db->prepare("\n            SELECT *\n            FROM category_pages\n            WHERE category_id = :category_id\n            LIMIT 1\n        ");

        $stmt->execute(['category_id' => $categoryId]);

        $page = $stmt->fetch(PDO::FETCH_ASSOC);

        return $page ?: null;
    }

    public function savePage(int $categoryId, array $data): int
    {
        $existing = $this->getPageByCategoryId($categoryId);

        if ($existing) {
            $stmt = $this->db->prepare("\n                UPDATE category_pages\n                SET\n                    slug = :slug,\n                    title = :title,\n                    meta_title = :meta_title,\n                    meta_description = :meta_description,\n                    h1 = :h1,\n                    intro_text = :intro_text,\n                    hero_image = :hero_image,\n                    content_top = :content_top,\n                    content_bottom = :content_bottom,\n                    faq_enabled = :faq_enabled,\n                    is_published = :is_published\n                WHERE category_id = :category_id\n            ");

            $stmt->execute([
                'category_id' => $categoryId,
                ...$data
            ]);

            return (int) $existing['id'];
        }

        $stmt = $this->db->prepare("\n            INSERT INTO category_pages\n            (\n                category_id,\n                slug,\n                title,\n                meta_title,\n                meta_description,\n                h1,\n                intro_text,\n                hero_image,\n                content_top,\n                content_bottom,\n                faq_enabled,\n                is_published\n            )\n            VALUES\n            (\n                :category_id,\n                :slug,\n                :title,\n                :meta_title,\n                :meta_description,\n                :h1,\n                :intro_text,\n                :hero_image,\n                :content_top,\n                :content_bottom,\n                :faq_enabled,\n                :is_published\n            )\n        ");

        $stmt->execute([
            'category_id' => $categoryId,
            ...$data
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Crea las reglas predeterminadas para una categoría
     * de tipo restaurante.
     */
    public function createRestaurantRules(int $categoryId): bool
    {
        if ($categoryId <= 0) {
            return false;
        }

        $sql = "
        INSERT INTO restaurant_category_rules
        (
            category_id,
            show_ingredients,
            show_combo_price,
            show_portion,
            show_spicy_level,
            show_allergens,
            show_featured,
            requires_inventory
        )
        VALUES
        (
            :category_id,
            1,
            1,
            1,
            1,
            1,
            1,
            1
        )
    ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'category_id' => $categoryId
        ]);
    }
}
