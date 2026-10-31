<?php
// app/Repositories/BranchImageRepository.php

class BranchImageRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtener todas las imágenes de una sede.
     */
    public function getByBranch(
        int $branchId
    ): array {

        $stmt = $this->db->prepare("
            SELECT
                id,
                branch_id,
                file_path,
                original_name,
                mime_type,
                file_size,
                width,
                height,
                sort_order,
                is_favorite,
                created_at,
                updated_at
            FROM branch_images
            WHERE branch_id = :branch_id
            ORDER BY
                sort_order ASC,
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
     * Crear imagen.
     */
    public function create(
        int $branchId,
        array $data
    ): int {

        $stmt = $this->db->prepare("
            INSERT INTO branch_images (
                branch_id,
                file_path,
                original_name,
                mime_type,
                file_size,
                width,
                height,
                sort_order,
                is_favorite
            )
            VALUES (
                :branch_id,
                :file_path,
                :original_name,
                :mime_type,
                :file_size,
                :width,
                :height,
                :sort_order,
                :is_favorite
            )
        ");

        $stmt->execute([
            ':branch_id' =>
                $branchId,

            ':file_path' =>
                $data['file_path'],

            ':original_name' =>
                $data['original_name'],

            ':mime_type' =>
                $data['mime_type'],

            ':file_size' =>
                (int) $data['file_size'],

            ':width' =>
                $data['width'] !== null
                    ? (int) $data['width']
                    : null,

            ':height' =>
                $data['height'] !== null
                    ? (int) $data['height']
                    : null,

            ':sort_order' =>
                (int) $data['sort_order'],

            ':is_favorite' =>
                !empty($data['is_favorite'])
                    ? 1
                    : 0
        ]);

        return (int)
        $this->db->lastInsertId();
    }


    /**
     * Actualizar orden y favorita.
     */
    public function updateMeta(
        int $imageId,
        int $branchId,
        int $sortOrder,
        bool $isFavorite
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE branch_images
            SET
                sort_order = :sort_order,
                is_favorite = :is_favorite
            WHERE id = :id
              AND branch_id = :branch_id
            LIMIT 1
        ");

        $stmt->execute([
            ':id' =>
                $imageId,

            ':branch_id' =>
                $branchId,

            ':sort_order' =>
                $sortOrder,

            ':is_favorite' =>
                $isFavorite ? 1 : 0
        ]);

        return $stmt->rowCount() >= 0;
    }


    /**
     * Eliminar imágenes específicas.
     */
    public function deleteByIds(
        int $branchId,
        array $imageIds
    ): bool {

        $imageIds =
            array_values(
                array_unique(
                    array_map(
                        'intval',
                        $imageIds
                    )
                )
            );

        $imageIds =
            array_values(
                array_filter(
                    $imageIds,
                    static fn(int $id): bool =>
                        $id > 0
                )
            );

        if (!$imageIds) {
            return true;
        }

        $placeholders =
            implode(
                ',',
                array_fill(
                    0,
                    count($imageIds),
                    '?'
                )
            );

        $sql = "
            DELETE FROM branch_images
            WHERE branch_id = ?
              AND id IN ($placeholders)
        ";

        $stmt =
            $this->db->prepare($sql);

        return $stmt->execute([
            $branchId,
            ...$imageIds
        ]);
    }
}