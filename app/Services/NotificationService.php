<?php

class NotificationService
{
    private NotificationRepository $repository;

    public function __construct()
    {
        $this->repository =
            new NotificationRepository();
    }

    /**
     * Lista las notificaciones del usuario actual.
     */
    public function getNotifications(
        int $userId,
        int $limit = 20
    ): array {

        return $this->repository->getByUser(
            $userId,
            $limit
        );
    }

    /**
     * Obtiene el contador de no leídas.
     */
    public function getUnreadCount(
        int $userId
    ): int {

        return $this->repository->countUnread(
            $userId
        );
    }

    /**
     * Crea una notificación.
     */
    public function create(
        int $userId,
        ?int $branchId,
        string $type,
        string $title,
        string $message,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): int {

        if ($userId <= 0) {
            throw new InvalidArgumentException(
                'El usuario de la notificación no es válido.'
            );
        }

        if (trim($title) === '') {
            throw new InvalidArgumentException(
                'El título de la notificación es obligatorio.'
            );
        }

        if (trim($message) === '') {
            throw new InvalidArgumentException(
                'El mensaje de la notificación es obligatorio.'
            );
        }

        return $this->repository->create([
            'user_id' =>
                $userId,

            'branch_id' =>
                $branchId,

            'type' =>
                $type,

            'title' =>
                $title,

            'message' =>
                $message,

            'reference_type' =>
                $referenceType,

            'reference_id' =>
                $referenceId
        ]);
    }

    /**
     * Marca una notificación como leída.
     */
    public function markAsRead(
        int $notificationId,
        int $userId
    ): bool {

        return $this->repository->markAsRead(
            $notificationId,
            $userId
        );
    }

    /**
     * Marca todas como leídas.
     */
    public function markAllAsRead(
        int $userId
    ): int {

        return $this->repository->markAllAsRead(
            $userId
        );
    }
}