<?php
// app/Repositories/ItemImageRepository.php

class ItemImageRepository
{
    private PDO $db;

    private string $table = 'item_images';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todas las imágenes activas de un producto.
     *
     * Orden:
     * 1. display_order
     * 2. id
     */
    public function getByItemId(int $itemId): array
    {
        if ($itemId <= 0) {
            return [];
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
            FROM {$this->table}
            WHERE item_id = :item_id
              AND is_active = 1
            ORDER BY
                display_order ASC,
                id ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'item_id' => $itemId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una imagen por ID.
     */
    public function find(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $sql = "
            SELECT
                *
            FROM {$this->table}
            WHERE id = :id
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
     * Crea una imagen para un producto.
     */
    public function create(
        int $itemId,
        string $imagePath,
        ?string $imageAlt = null,
        int $displayOrder = 0,
        bool $isFavorite = false
    ): int {

        $sql = "
            INSERT INTO {$this->table}
            (
                item_id,
                image_path,
                image_alt,
                display_order,
                is_favorite,
                is_active
            )
            VALUES
            (
                :item_id,
                :image_path,
                :image_alt,
                :display_order,
                :is_favorite,
                1
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'item_id'      => $itemId,
            'image_path'   => $imagePath,
            'image_alt'    => $imageAlt,
            'display_order'=> $displayOrder,
            'is_favorite'  => $isFavorite ? 1 : 0
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza los datos de una imagen.
     */
    public function update(
        int $id,
        array $data
    ): bool {

        if ($id <= 0 || empty($data)) {
            return false;
        }

        $allowedFields = [
            'image_path',
            'image_alt',
            'display_order',
            'is_favorite',
            'is_active'
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
            SET {$setClause}
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Cambia el orden de una imagen.
     */
    public function updateOrder(
        int $id,
        int $displayOrder
    ): bool {

        $sql = "
            UPDATE {$this->table}
            SET display_order = :display_order
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id'            => $id,
            'display_order' => $displayOrder
        ]);
    }

    /**
     * Establece una sola imagen como favorita/principal.
     *
     * Primero quita la favorita de todas las imágenes
     * del producto y posteriormente establece la indicada.
     */
    public function setFavorite(
        int $itemId,
        int $imageId
    ): bool {

        if ($itemId <= 0 || $imageId <= 0) {
            return false;
        }

        try {

            $this->db->beginTransaction();

            /*
             * Quitar favorita a todas las imágenes
             * pertenecientes al producto.
             */
            $sqlReset = "
                UPDATE {$this->table}
                SET is_favorite = 0
                WHERE item_id = :item_id
            ";

            $stmtReset = $this->db->prepare($sqlReset);

            $stmtReset->execute([
                'item_id' => $itemId
            ]);


            /*
             * Establecer la imagen seleccionada
             * como favorita.
             */
            $sqlFavorite = "
                UPDATE {$this->table}
                SET is_favorite = 1
                WHERE id = :image_id
                  AND item_id = :item_id
                  AND is_active = 1
            ";

            $stmtFavorite = $this->db->prepare($sqlFavorite);

            $stmtFavorite->execute([
                'image_id' => $imageId,
                'item_id'  => $itemId
            ]);

            if ($stmtFavorite->rowCount() === 0) {
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
     * Desactiva lógicamente una imagen.
     */
    public function deactivate(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $sql = "
            UPDATE {$this->table}
            SET is_active = 0,
                is_favorite = 0
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id
        ]);
    }

    /**
     * Elimina físicamente una imagen.
     *
     * Se utilizará únicamente cuando el servicio determine
     * que también debe eliminarse el archivo físico.
     */
    public function delete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $sql = "
            DELETE FROM {$this->table}
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id
        ]);
    }

    /**
     * Cuenta las imágenes activas de un producto.
     */
    public function countByItemId(int $itemId): int
    {
        if ($itemId <= 0) {
            return 0;
        }

        $sql = "
            SELECT COUNT(*)
            FROM {$this->table}
            WHERE item_id = :item_id
              AND is_active = 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'item_id' => $itemId
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Obtiene la imagen favorita/principal del producto.
     */
    public function getFavoriteByItemId(int $itemId): ?array
    {
        if ($itemId <= 0) {
            return null;
        }

        $sql = "
            SELECT
                *
            FROM {$this->table}
            WHERE item_id = :item_id
              AND is_active = 1
              AND is_favorite = 1
            ORDER BY id ASC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'item_id' => $itemId
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }
}