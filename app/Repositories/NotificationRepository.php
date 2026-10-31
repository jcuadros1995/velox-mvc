<?php

class NotificationRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene las notificaciones de un usuario.
     */
    public function getByUser(
        int $userId,
        int $limit = 20
    ): array {

        $limit = max(1, min($limit, 100));

        $sql = "
            SELECT
                id,
                user_id,
                branch_id,
                type,
                title,
                message,
                reference_type,
                reference_id,
                is_read,
                read_at,
                created_at
            FROM notifications
            WHERE user_id = :user_id
            ORDER BY created_at DESC
            LIMIT {$limit}
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta las notificaciones no leídas.
     */
    public function countUnread(
        int $userId
    ): int {

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM notifications
            WHERE user_id = :user_id
              AND is_read = 0
        ");

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Crea una notificación.
     */
    public function create(
        array $data
    ): int {

        $stmt = $this->db->prepare("
            INSERT INTO notifications (
                user_id,
                branch_id,
                type,
                title,
                message,
                reference_type,
                reference_id,
                is_read,
                created_at
            )
            VALUES (
                :user_id,
                :branch_id,
                :type,
                :title,
                :message,
                :reference_type,
                :reference_id,
                0,
                NOW()
            )
        ");

        $stmt->execute([
            ':user_id' =>
                (int) $data['user_id'],

            ':branch_id' =>
                $data['branch_id'] !== null
                    ? (int) $data['branch_id']
                    : null,

            ':type' =>
                trim((string) $data['type']),

            ':title' =>
                trim((string) $data['title']),

            ':message' =>
                trim((string) $data['message']),

            ':reference_type' =>
                $data['reference_type'] ?? null,

            ':reference_id' =>
                $data['reference_id'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Marca una notificación como leída.
     */
    public function markAsRead(
        int $notificationId,
        int $userId
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE notifications
            SET
                is_read = 1,
                read_at = NOW()
            WHERE id = :id
              AND user_id = :user_id
              AND is_read = 0
        ");

        $stmt->execute([
            ':id' => $notificationId,
            ':user_id' => $userId
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Marca todas las notificaciones como leídas.
     */
    public function markAllAsRead(
        int $userId
    ): int {

        $stmt = $this->db->prepare("
            UPDATE notifications
            SET
                is_read = 1,
                read_at = NOW()
            WHERE user_id = :user_id
              AND is_read = 0
        ");

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->rowCount();
    }
}