<?php

class CategoryPageRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todas las categorías con información
     * de su página pública.
     */
    public function getCategoriesWithPages(): array
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

                cp.id AS page_id,
                cp.title AS page_title,
                cp.intro_text AS page_description,
                cp.is_published,

                (
                    SELECT COUNT(*)
                    FROM items i
                    WHERE i.category_id = c.id
                      AND i.is_active = 1
                ) AS product_count,

                (
                    SELECT COUNT(*)
                    FROM category_page_images cpi
                    WHERE cpi.category_page_id = cp.id
                      AND cpi.is_active = 1
                ) AS image_count,

                (
                    SELECT cpi.image_path
                    FROM category_page_images cpi
                    WHERE cpi.category_page_id = cp.id
                      AND cpi.is_active = 1
                    ORDER BY
                        cpi.is_favorite DESC,
                        cpi.display_order ASC,
                        cpi.id ASC
                    LIMIT 1
                ) AS favorite_image

            FROM categories c

            LEFT JOIN category_pages cp
                ON cp.category_id = c.id

            WHERE c.is_active = 1

            ORDER BY
                c.parent_id IS NOT NULL ASC,
                c.name ASC
        ";

        return $this->db
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una categoría.
     */
    public function getCategory(int $categoryId): ?array
    {
        $sql = "
            SELECT
                id,
                parent_id,
                name,
                slug,
                catalog_type,
                description,
                is_active
            FROM categories
            WHERE id = :id
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
     * Obtiene la página de una categoría.
     */
    public function getPageByCategoryId(
        int $categoryId
    ): ?array {

        $sql = "
            SELECT *
            FROM category_pages
            WHERE category_id = :category_id
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
     * Crea o actualiza la configuración básica
     * de la página.
     */
    public function savePage(
        int $categoryId,
        string $title,
        string $description
    ): int {

        $existing =
            $this->getPageByCategoryId(
                $categoryId
            );

        $category =
            $this->getCategory(
                $categoryId
            );

        if (!$category) {
            throw new InvalidArgumentException(
                'La categoría no existe.'
            );
        }

        /*
         * El slug se genera internamente.
         * El usuario nunca lo ve.
         */
        $slug =
            $category['slug'] .
            '-bogota';

        if ($existing) {

            $sql = "
                UPDATE category_pages
                SET
                    title = :title,
                    intro_text = :intro_text,
                    slug = :slug
                WHERE id = :id
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                'title'       => $title,
                'intro_text'  => $description,
                'slug'        => $slug,
                'id'          => $existing['id']
            ]);

            return (int) $existing['id'];
        }

        $sql = "
            INSERT INTO category_pages
            (
                category_id,
                slug,
                title,
                intro_text,
                is_published
            )
            VALUES
            (
                :category_id,
                :slug,
                :title,
                :intro_text,
                1
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'category_id' => $categoryId,
            'slug'        => $slug,
            'title'       => $title,
            'intro_text'  => $description
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Obtiene imágenes.
     */
    public function getImages(
        int $pageId
    ): array {


        $sql = "
            SELECT
                id,
                category_page_id,
                image_path,
                mobile_image_path,
                image_alt,
                display_order,
                is_favorite,
                is_active
            FROM category_page_images
            WHERE category_page_id = :page_id
              AND is_active = 1
            ORDER BY
                display_order ASC,
                id ASC
        ";


        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'page_id' => $pageId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crea una imagen.
     */
    public function createImage(
        int $pageId,
        string $path,
        string $alt,
        int $order
    ): int {

        $sql = "
            INSERT INTO category_page_images
            (
                category_page_id,
                image_path,
                image_alt,
                display_order,
                is_favorite,
                is_active
            )
            VALUES
            (
                :page_id,
                :path,
                :alt,
                :display_order,
                0,
                1
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'page_id'       => $pageId,
            'path'          => $path,
            'alt'           => $alt,
            'display_order' => $order
        ]);

        return (int) $this->db->lastInsertId();
    }


    /**
     * Guarda la versión móvil de una imagen de categoría.
     */
    public function updateMobileImagePath(
        int $imageId,
        int $pageId,
        string $mobilePath
    ): bool {
        $sql = "
        UPDATE category_page_images
        SET mobile_image_path = :mobile_path
        WHERE id = :image_id
          AND category_page_id = :page_id
          AND is_active = 1
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'mobile_path' => $mobilePath,
            'image_id'    => $imageId,
            'page_id'     => $pageId
        ]);

        return $stmt->rowCount() > 0;
    }


    /**
     * Elimina una imagen.
     */
    public function deleteImage(
        int $imageId
    ): ?array {

        $sql = "
            SELECT *
            FROM category_page_images
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $imageId
        ]);

        $image =
            $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$image) {
            return null;
        }

        $delete = $this->db->prepare("
            DELETE FROM category_page_images
            WHERE id = :id
        ");

        $delete->execute([
            'id' => $imageId
        ]);

        return $image;
    }

    /**
     * Cambia la imagen principal.
     */
    public function setFavorite(
        int $pageId,
        int $imageId
    ): bool {

        try {

            $this->db->beginTransaction();

            $reset = $this->db->prepare("
                UPDATE category_page_images
                SET is_favorite = 0
                WHERE category_page_id = :page_id
            ");

            $reset->execute([
                'page_id' => $pageId
            ]);

            $favorite = $this->db->prepare("
                UPDATE category_page_images
                SET is_favorite = 1
                WHERE id = :image_id
                  AND category_page_id = :page_id
                  AND is_active = 1
            ");

            $favorite->execute([
                'image_id' => $imageId,
                'page_id'  => $pageId
            ]);

            if ($favorite->rowCount() === 0) {

                $this->db->rollBack();

                return false;
            }

            $this->db->commit();

            return true;

        } catch (Throwable $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return false;
        }
    }

    /**
     * Actualiza el orden.
     */
    public function updateOrder(
        int $imageId,
        int $order
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE category_page_images
            SET display_order = :display_order
            WHERE id = :id
        ");

        return $stmt->execute([
            'id'            => $imageId,
            'display_order' => $order
        ]);
    }

    public function getPageById(
        int $pageId
    ): ?array {

        $sql = "
        SELECT *
        FROM category_pages
        WHERE id = :id
        LIMIT 1
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $pageId
        ]);

        $result =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Obtiene las páginas públicas activas.
     *
     * Solo devuelve páginas cuya categoría:
     *
     * - esté activa
     * - tenga página publicada
     *
     * Se utiliza para construir el menú público
     * de Favoritos.
     */
    public function getActivePublicPages(): array
    {
        $sql = "
        SELECT
            cp.id,
            cp.category_id,
            cp.slug,
            cp.title,
            cp.h1,
            cp.hero_image,

            c.name AS category_name,
            c.slug AS category_slug

        FROM category_pages cp

        INNER JOIN categories c
            ON c.id = cp.category_id

        WHERE
            cp.is_published = 1
            AND c.is_active = 1

        ORDER BY
            cp.title ASC,
            cp.id ASC
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una página pública por su slug.
     *
     * Solo devuelve páginas:
     *
     * - publicadas
     * - pertenecientes a una categoría activa
     */
    public function getPublicPageBySlug(
        string $slug
    ): ?array {

        $slug =
            trim(
                $slug
            );

        if (
            $slug === ''
        ) {

            return null;
        }


        $sql = "
        SELECT

            cp.id,
            cp.category_id,
            cp.slug,
            cp.title,
            cp.meta_title,
            cp.meta_description,
            cp.h1,
            cp.intro_text,
            cp.hero_image,
            cp.content_top,
            cp.content_bottom,
            cp.faq_enabled,
            cp.is_published,

            c.name AS category_name,
            c.slug AS category_slug,
            c.description AS category_description,
            c.is_active AS category_is_active

        FROM category_pages cp

        INNER JOIN categories c
            ON c.id = cp.category_id

        WHERE
            cp.slug = :slug
            AND cp.is_published = 1
            AND c.is_active = 1

        LIMIT 1
    ";


        $stmt =
            $this->db->prepare(
                $sql
            );


        $stmt->execute([
            'slug' =>
                $slug
        ]);


        $result =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );


        return $result ?: null;
    }

    /**
     * Obtiene los productos activos de una categoría
     * para la página pública.
     *
     * Incluye:
     * - información del producto
     * - imagen principal
     * - reglas de la categoría
     */
    public function getPublicProductsByCategory(
        int $categoryId
    ): array {

        if ($categoryId <= 0) {
            return [];
        }

        $sql = "
    SELECT

        i.id,
        i.category_id,
        i.type,
        i.sku,
        i.name,
        i.slug,
        i.description,
        i.ingredients,
        i.price,
        i.combo_price,
        i.portion_size,
        i.spicy_level,
        i.allergens,
        i.preparation_notes,
        i.is_active,
        i.is_featured,
        i.is_available,
        i.display_order,

        (
            SELECT
                ii.image_path

            FROM item_images ii

            WHERE
                ii.item_id = i.id
                AND ii.is_active = 1

            ORDER BY
                ii.is_favorite DESC,
                ii.display_order ASC,
                ii.id ASC

            LIMIT 1

        ) AS favorite_image,

        (
            SELECT
                ii.image_alt

            FROM item_images ii

            WHERE
                ii.item_id = i.id
                AND ii.is_active = 1

            ORDER BY
                ii.is_favorite DESC,
                ii.display_order ASC,
                ii.id ASC

            LIMIT 1

        ) AS favorite_image_alt

        FROM items i
    
        INNER JOIN categories c
            ON c.id = i.category_id
    
        WHERE
        (
            i.category_id = :category_id
            OR c.parent_id = :parent_category_id
        )
    
        AND i.is_active = 1
    
        AND c.is_active = 1
    
        ORDER BY
    
            i.is_featured DESC,
            i.display_order ASC,
            i.id ASC
        ";

        $stmt =
            $this->db->prepare(
                $sql
            );

        $stmt->execute([
            'category_id'        => $categoryId,
            'parent_category_id' => $categoryId
        ]);

        return
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );
    }

    /**
     * Obtiene las opciones de precio de varios productos.
     */
    public function getPublicPriceOptionsByItemIds(
        array $itemIds
    ): array {

        $itemIds =
            array_values(
                array_unique(
                    array_filter(
                        array_map(
                            'intval',
                            $itemIds
                        ),
                        fn ($id) => $id > 0
                    )
                )
            );


        if (empty($itemIds)) {
            return [];
        }


        $placeholders =
            implode(
                ',',
                array_fill(
                    0,
                    count($itemIds),
                    '?'
                )
            );


        $sql = "
        SELECT
            id,
            item_id,
            quantity,
            quantity_label,
            price,
            combo_price,
            display_order

        FROM item_price_options

        WHERE
            item_id IN ($placeholders)

            AND is_active = 1

        ORDER BY
            item_id ASC,
            display_order ASC,
            quantity ASC,
            id ASC
    ";


        $stmt =
            $this->db->prepare(
                $sql
            );


        $stmt->execute(
            $itemIds
        );


        $rows =
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );


        $result = [];


        foreach (
            $rows as $row
        ) {

            $itemId =
                (int) $row['item_id'];


            if (
                !isset(
                    $result[$itemId]
                )
            ) {

                $result[$itemId] = [];

            }


            $result[$itemId][] =
                $row;
        }


        return $result;
    }

    /**
     * Obtiene las reglas públicas de una categoría.
     */
    public function getPublicCategoryRules(
        int $categoryId
    ): array {

        if ($categoryId <= 0) {
            return [
                'show_ingredients' => 1,
                'show_combo_price' => 1,
                'show_portion' => 0,
                'show_spicy_level' => 0,
                'show_allergens' => 0,
                'show_featured' => 1
            ];
        }

        $sql = "
        SELECT

            show_ingredients,
            show_combo_price,
            show_portion,
            show_spicy_level,
            show_allergens,
            show_featured

        FROM restaurant_category_rules

        WHERE category_id = :category_id

        LIMIT 1
    ";

        $stmt =
            $this->db->prepare(
                $sql
            );

        $stmt->execute([
            'category_id' =>
                $categoryId
        ]);

        $rules =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );

        if (!$rules) {

            return [
                'show_ingredients' => 1,
                'show_combo_price' => 1,
                'show_portion' => 0,
                'show_spicy_level' => 0,
                'show_allergens' => 0,
                'show_featured' => 1
            ];
        }

        return $rules;
    }
}