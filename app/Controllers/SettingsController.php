<?php

// app/Controllers/SettingsController.php

class SettingsController extends Controller
{
    private SettingsService $settingsService;
    private UserRepository $userRepository;
    private SecurityService $securityService;
    public function __construct()
    {
        $this->settingsService =
            new SettingsService();

        $this->userRepository =
            new UserRepository();

        $this->securityService =
            new SecurityService();
    }

    /**
     * GET /admin/settings
     *
     * SuperAdmin:
     * - Puede ver y editar.
     *
     * Admin:
     * - Puede ver solamente.
     *
     * Staff:
     * - No puede acceder.
     */
    public function index(): void
    {
        $user = Auth::user();

        if (!$user) {
            http_response_code(401);

            return;
        }

        $role = strtolower(
            (string) ($user['role_name'] ?? '')
        );

        /*
         * Solo SuperAdmin y Admin pueden
         * consultar la configuración general.
         */
        if (
            $role !== 'superadmin' &&
            $role !== 'admin'
        ) {
            http_response_code(403);

            $this->view('admin/layout', [
                'contentView' => __DIR__ . '/../Views/errors/403.php'
            ]);

            return;
        }

        /*
         * La configuración que mostramos aquí
         * SIEMPRE es la configuración GLOBAL.
         *
         * No usamos el branch_id del Admin.
         */
        $settings = $this->settingsService->getGroupedSettings(
            null
        );

        $this->view('admin/layout', [
            'contentView' => __DIR__ . '/../Views/settings/index.php',

            'settings' => $settings,

            'branchId' => null,

            'settingsCanEdit' =>
                $role === 'superadmin',

            'settingsIsReadOnly' =>
                $role !== 'superadmin'
        ]);
    }

    /**
     * POST /api/settings/update
     *
     * Solo SuperAdmin puede modificar
     * la configuración general.
     */
    public function update(): void
    {
        header(
            'Content-Type: application/json; charset=utf-8'
        );

        $user = Auth::user();

        if (!$user) {
            http_response_code(401);

            echo json_encode([
                'success' => false,
                'message' => 'Sesión no válida.'
            ]);

            return;
        }

        $role = strtolower(
            (string) ($user['role_name'] ?? '')
        );

        /*
         * SOLO SUPERADMIN puede modificar.
         */
        if ($role !== 'superadmin') {

            http_response_code(403);

            echo json_encode([
                'success' => false,
                'message' =>
                    'Solo el SuperAdmin puede modificar la configuración general.'
            ]);

            return;
        }

        try {

            if (
                ($_SERVER['REQUEST_METHOD'] ?? 'GET')
                !== 'POST'
            ) {

                http_response_code(405);

                echo json_encode([
                    'success' => false,
                    'message' =>
                        'Método HTTP no permitido.'
                ]);

                return;
            }

            /*
             * La configuración general siempre
             * se guarda con branch_id = NULL.
             */
            $processedSettings =
                $this->settingsService->saveSettings(
                    null,
                    $_POST,
                    $_FILES
                );

            if ($processedSettings === 0) {

                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' =>
                        'No se recibieron configuraciones para guardar.'
                ]);

                return;
            }

            /*
             * Procesar cierre global de sesiones.
             */
            $forceLogoutExecuted =
                $this->securityService
                    ->consumeForceLogout();

            echo json_encode([
                'success' => true,
                'message' =>
                    'La configuración fue actualizada correctamente.',
                'force_logout' =>
                    $forceLogoutExecuted
            ]);
        } catch (Throwable $e) {

            error_log(
                '[SETTINGS_ERROR] ' .
                $e->getMessage() .
                ' | ' .
                $e->getFile() .
                ':' .
                $e->getLine()
            );

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' =>
                    'No fue posible actualizar la configuración.'
            ]);
        }
    }

    /**
     * Obtiene branch_id de la sesión.
     */
    private function getBranchId(): ?int
    {
        if (
            isset($_SESSION['branch_id']) &&
            $_SESSION['branch_id'] !== null &&
            $_SESSION['branch_id'] !== ''
        ) {
            return (int) $_SESSION['branch_id'];
        }

        return null;
    }

    /**
     * Comprueba si el usuario tiene un permiso concreto.
     */
    private function hasPermission(string $permission): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        $permissions = $this->userRepository->getPermissionsByRoleId(
            isset($user['role_id'])
                ? (int) $user['role_id']
                : null
        );

        return in_array($permission, $permissions, true);
    }
}