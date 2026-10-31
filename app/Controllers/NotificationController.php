<?php

class NotificationController extends Controller
{
    private NotificationService $service;

    public function __construct()
    {
        $this->service = new NotificationService();
    }

    /**
     * GET /api/admin/notifications
     */
    public function index(): void
    {
        try {
            $userId = (int) ($_SESSION['user_id'] ?? 0);

            if ($userId <= 0) {
                $this->json([
                    'success' => false,
                    'message' => 'Sesión no válida.'
                ], 401);

                return;
            }

            $limit = (int) ($_GET['limit'] ?? 20);

            $notifications = $this->service->getNotifications(
                $userId,
                $limit
            );

            $this->json([
                'success' => true,
                'data' => $notifications
            ]);
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'No fue posible cargar las notificaciones.'
            ], 500);
        }
    }

    /**
     * GET /api/admin/notifications/unread-count
     */
    public function unreadCount(): void
    {
        try {
            $userId = (int) ($_SESSION['user_id'] ?? 0);

            if ($userId <= 0) {
                $this->json([
                    'success' => false,
                    'message' => 'Sesión no válida.'
                ], 401);

                return;
            }

            $count = $this->service->getUnreadCount($userId);

            $this->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'No fue posible consultar las notificaciones.'
            ], 500);
        }
    }

    /**
     * POST /api/admin/notifications/read
     */
    public function read(): void
    {
        try {
            $userId = (int) ($_SESSION['user_id'] ?? 0);
            $notificationId = (int) ($_POST['id'] ?? 0);

            if ($userId <= 0 || $notificationId <= 0) {
                $this->json([
                    'success' => false,
                    'message' => 'Datos inválidos.'
                ], 400);

                return;
            }

            $updated = $this->service->markAsRead(
                $notificationId,
                $userId
            );

            $this->json([
                'success' => true,
                'updated' => $updated
            ]);
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'No fue posible marcar la notificación.'
            ], 500);
        }
    }

    /**
     * POST /api/admin/notifications/read-all
     */
    public function readAll(): void
    {
        try {
            $userId = (int) ($_SESSION['user_id'] ?? 0);

            if ($userId <= 0) {
                $this->json([
                    'success' => false,
                    'message' => 'Sesión no válida.'
                ], 401);

                return;
            }

            $updated = $this->service->markAllAsRead($userId);

            $this->json([
                'success' => true,
                'updated' => $updated
            ]);
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'No fue posible actualizar las notificaciones.'
            ], 500);
        }
    }
}