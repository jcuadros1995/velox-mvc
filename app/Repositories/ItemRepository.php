<?php
// app/Repositories/ItemRepository.php

class ItemRepository
{
    private PDO $db;

    private string $table = 'items';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene productos.
     *
     * $includeInactive = false
     *   -> solamente activos
     *
     * $includeInactive = true
     *   -> activos e inactivos
     */
    public function getAllWithCategory(
        bool $includeInactive = false
    ): array {

        $where = '';

        if (!$includeInactive) {
            $where = 'WHERE i.is_active = 1';
        }

        $sql = "
        SELECT
            i.*,

            c.name AS category_name,
            c.parent_id AS category_parent_id,

            (
                SELECT ii.image_path
                FROM item_images ii
                WHERE ii.item_id = i.id
                  AND ii.is_active = 1
                ORDER BY
                    ii.is_favorite DESC,
                    ii.display_order ASC,
                    ii.id ASC
                LIMIT 1
            ) AS favorite_image,

            (
                SELECT COUNT(*)
                FROM item_images ii2
                WHERE ii2.item_id = i.id
                  AND ii2.is_active = 1
            ) AS image_count

        FROM {$this->table} i

        LEFT JOIN categories c
            ON c.id = i.category_id

        {$where}

        ORDER BY
            i.display_order ASC,
            i.id DESC
    ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca un producto por ID.
     */
    public function find(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $sql = "
            SELECT
                i.*,
                c.name AS category_name,
                c.parent_id AS category_parent_id

            FROM {$this->table} i

            LEFT JOIN categories c
                ON c.id = i.category_id

            WHERE i.id = :id

            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Crea un nuevo producto.
     */
    public function create(array $data): int
    {
        if (empty($data)) {
            throw new InvalidArgumentException(
                'No se proporcionaron datos para crear el producto.'
            );
        }

        $allowedFields = [
            'category_id',
            'type',
            'sku',
            'name',
            'slug',
            'description',
            'ingredients',
            'price',
            'combo_price',
            'portion_size',
            'spicy_level',
            'allergens',
            'preparation_notes',
            'cost_price',
            'tax_rate',
            'duration_minutes',
            'is_active',
            'is_featured',
            'is_available',
            'display_order'
        ];

        $filteredData = [];

        foreach ($data as $key => $value) {

            if (in_array($key, $allowedFields, true)) {
                $filteredData[$key] = $value;
            }
        }

        if (empty($filteredData)) {
            throw new InvalidArgumentException(
                'No existen campos válidos para crear el producto.'
            );
        }

        $fields = array_keys($filteredData);

        $fieldNames = implode(', ', $fields);

        $placeholders = ':' . implode(
                ', :',
                $fields
            );

        $sql = "
            INSERT INTO {$this->table}
            (
                {$fieldNames}
            )
            VALUES
            (
                {$placeholders}
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute($filteredData);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza un producto existente.
     */
    public function update(
        int $id,
        array $data
    ): bool {

        if ($id <= 0 || empty($data)) {
            return false;
        }

        $allowedFields = [
            'category_id',
            'type',
            'sku',
            'name',
            'slug',
            'description',
            'ingredients',
            'price',
            'combo_price',
            'portion_size',
            'spicy_level',
            'allergens',
            'preparation_notes',
            'cost_price',
            'tax_rate',
            'duration_minutes',
            'is_active',
            'is_featured',
            'is_available',
            'display_order'
        ];

        $fields = [];
        $params = [
            'id' => $id
        ];

        foreach ($data as $key => $value) {

            if (!in_array($key, $allowedFields, true)) {
                continue;
            }

            $fields[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }

        if (empty($fields)) {
            return false;
        }

        $setClause = implode(', ', $fields);

        $sql = "
            UPDATE {$this->table}
            SET
                {$setClause}
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Obtiene un producto junto con sus imágenes.
     */
    public function getWithImages(int $id): ?array
    {
        $item = $this->find($id);

        if (!$item) {
            return null;
        }

        $sql = "
            SELECT
                id,
                item_id,
                image_path,
                image_alt,
                display_order,
                is_favorite,
                is_active,
                created_at,
                updated_at
            FROM item_images
            WHERE item_id = :item_id
              AND is_active = 1
            ORDER BY
                display_order ASC,
                id ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'item_id' => $id
        ]);

        $item['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $item['price_options'] =
            $this->getPriceOptionsByItemId($id);

        return $item;
    }

    /**
     * Obtiene las opciones de precio de un producto.
     */
    public function getPriceOptionsByItemId(
        int $itemId
    ): array {

        if ($itemId <= 0) {
            return [];
        }

        $sql = "
        SELECT
            id,
            item_id,
            quantity,
            quantity_label,
            price,
            combo_price,
            display_order,
            is_active,
            created_at,
            updated_at
        FROM item_price_options
        WHERE item_id = :item_id
          AND is_active = 1
        ORDER BY
            display_order ASC,
            quantity ASC,
            id ASC
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'item_id' => $itemId
        ]);

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }


    /**
     * Guarda las opciones de precio de un producto.
     *
     * Recibe el listado completo y reemplaza
     * las opciones anteriores.
     */
    public function syncPriceOptions(
        int $itemId,
        array $options
    ): bool {

        if ($itemId <= 0) {
            return false;
        }

        try {

            $this->db->beginTransaction();

            /*
             * Eliminamos las opciones actuales.
             */
            $stmtDelete = $this->db->prepare("
            DELETE FROM item_price_options
            WHERE item_id = :item_id
        ");

            $stmtDelete->execute([
                'item_id' => $itemId
            ]);


            /*
             * Si no hay opciones nuevas,
             * simplemente dejamos el producto sin ellas.
             */
            if (empty($options)) {

                $this->db->commit();

                return true;
            }


            $stmtInsert = $this->db->prepare("
            INSERT INTO item_price_options
            (
                item_id,
                quantity,
                quantity_label,
                price,
                combo_price,
                display_order,
                is_active
            )
            VALUES
            (
                :item_id,
                :quantity,
                :quantity_label,
                :price,
                :combo_price,
                :display_order,
                1
            )
        ");


            foreach (
                $options as $index => $option
            ) {

                $quantity =
                    (int) (
                        $option['quantity']
                        ?? 0
                    );

                $label =
                    trim(
                        (string) (
                            $option['quantity_label']
                            ?? ''
                        )
                    );

                $price =
                    (float) (
                        $option['price']
                        ?? 0
                    );

                $comboPrice =
                    $option['combo_price']
                    ?? null;

                if (
                    $quantity <= 0 ||
                    $price < 0
                ) {
                    continue;
                }

                if (
                    $comboPrice !== null &&
                    $comboPrice !== '' &&
                    (float) $comboPrice < 0
                ) {
                    $comboPrice = null;
                }

                if (
                    $comboPrice === ''
                ) {
                    $comboPrice = null;
                }

                $stmtInsert->execute([
                    'item_id' =>
                        $itemId,

                    'quantity' =>
                        $quantity,

                    'quantity_label' =>
                        $label !== ''
                            ? $label
                            : $quantity . ' UNIDADES',

                    'price' =>
                        $price,

                    'combo_price' =>
                        $comboPrice,

                    'display_order' =>
                        $index
                ]);
            }

            $this->db->commit();

            return true;

        } catch (Throwable $e) {

            if (
                $this->db->inTransaction()
            ) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Obtiene las reglas dinámicas de una categoría.
     */
    public function getCategoryRules(int $categoryId): ?array
    {
        if ($categoryId <= 0) {
            return null;
        }

        $sql = "
            SELECT
                r.category_id,
                r.show_ingredients,
                r.show_combo_price,
                r.show_portion,
                r.show_spicy_level,
                r.show_allergens,
                r.show_featured,
                r.requires_inventory

            FROM restaurant_category_rules r

            WHERE r.category_id = :category_id

            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'category_id' => $categoryId
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Obtiene únicamente las categorías configuradas
     * para Burger Town.
     */
    public function getRestaurantCategories(): array
    {
        $sql = "
        SELECT
            c.id,
            c.parent_id,
            c.name,
            c.slug,
            c.description,
            c.is_active,

            r.show_ingredients,
            r.show_combo_price,
            r.show_portion,
            r.show_spicy_level,
            r.show_allergens,
            r.show_featured,
            r.requires_inventory

        FROM categories c

        INNER JOIN restaurant_category_rules r
            ON r.category_id = c.id

        WHERE
            c.is_active = 1
            AND c.catalog_type = 'restaurant'

        ORDER BY
            CASE
                WHEN c.parent_id IS NULL THEN 0
                ELSE 1
            END,
            c.parent_id ASC,
            c.name ASC
    ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene la categoría por ID.
     */
    public function getCategoryById(int $categoryId): ?array
    {
        if ($categoryId <= 0) {
            return null;
        }

        $sql = "
            SELECT
                c.id,
                c.parent_id,
                c.name,
                c.slug,
                c.description,
                c.is_active
            FROM categories c
            WHERE c.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $categoryId
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Determina si el producto tiene historial de pedidos.
     *
     * Esto será importante para decidir si podemos eliminarlo
     * físicamente o debemos realizar una baja lógica.
     */
    public function hasOrderHistory(int $itemId): bool
    {
        if ($itemId <= 0) {
            return false;
        }

        $sql = "
            SELECT EXISTS(
                SELECT 1
                FROM order_items
                WHERE item_id = :item_id
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'item_id' => $itemId
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Desactiva lógicamente un producto.
     *
     * No elimina el registro de items.
     */
    public function deactivate(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $sql = "
            UPDATE {$this->table}
            SET
                is_active = 0,
                is_available = 0
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id
        ]);
    }

    /**
     * Activa un producto.
     */
    public function activate(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $sql = "
            UPDATE {$this->table}
            SET
                is_active = 1,
                is_available = 1
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id
        ]);
    }

    /**
     * Obtiene los atributos tradicionales del producto.
     *
     * Se mantiene por compatibilidad con el sistema anterior.
     */
    public function getAttributes(int $itemId): array
    {
        $sql = "
            SELECT
                a.id AS attribute_id,
                a.name,
                a.type,
                ia.attribute_value
            FROM item_attributes ia

            INNER JOIN attributes a
                ON ia.attribute_id = a.id

            WHERE ia.item_id = :item_id

            ORDER BY a.name ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'item_id' => $itemId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crea o actualiza un atributo del producto.
     */
    public function addAttribute(
        int $itemId,
        int $attributeId,
        string $value
    ): bool {

        $sql = "
            INSERT INTO item_attributes
            (
                item_id,
                attribute_id,
                attribute_value
            )
            VALUES
            (
                :item_id,
                :attribute_id,
                :attribute_value
            )
            ON DUPLICATE KEY UPDATE
                attribute_value = :attribute_value_update
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'item_id' => $itemId,
            'attribute_id' => $attributeId,
            'attribute_value' => $value,
            'attribute_value_update' => $value
        ]);
    }
}