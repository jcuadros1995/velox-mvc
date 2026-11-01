<?php

/**
 * Registro centralizado de actividad del panel administrativo.
 * Nunca almacena cuerpos POST, contraseñas, tokens ni query strings.
 */
class AuditLogger
{
    public static function record(string $method, string $path, string $handler): void
    {
        try {
            $user = Auth::user() ?? [];
            $userId = isset($user['id']) ? (int) $user['id'] : null;
            $userName = trim((string) (
                $user['name']
                ?? $user['full_name']
                ?? $user['user_name']
                ?? trim((string)($user['first_name'] ?? '') . ' ' . (string)($user['last_name'] ?? ''))
                ?? ''
            ));
            $email = trim((string)($user['email'] ?? ''));
            $role = trim((string)($user['role_name'] ?? ''));
            $branchId = isset($user['branch_id']) ? (int)$user['branch_id'] : null;

            [$module, $action] = self::describe($method, $path, $handler);
            $ip = self::clientIp();

            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO admin_audit_logs
                    (user_id, user_name, user_email, user_role, branch_id,
                     module, action, http_method, route, ip_address, user_agent,
                     response_status, created_at)
                VALUES
                    (:user_id, :user_name, :user_email, :user_role, :branch_id,
                     :module, :action, :http_method, :route, :ip_address, :user_agent,
                     200, NOW())
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':user_name' => $userName !== '' ? $userName : ($email !== '' ? $email : 'Usuario'),
                ':user_email' => $email !== '' ? $email : null,
                ':user_role' => $role !== '' ? $role : null,
                ':branch_id' => $branchId,
                ':module' => $module,
                ':action' => $action,
                ':http_method' => strtoupper($method),
                ':route' => substr($path, 0, 255),
                ':ip_address' => $ip,
                ':user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500)
            ]);
            $id = (int)$db->lastInsertId();

            register_shutdown_function(static function () use ($id): void {
                try {
                    $status = http_response_code();
                    if ($status === false) $status = 200;
                    $stmt = Database::getConnection()->prepare(
                        "UPDATE admin_audit_logs SET response_status = :status WHERE id = :id"
                    );
                    $stmt->execute([':status' => (int)$status, ':id' => $id]);
                } catch (Throwable $e) {
                    error_log('[AUDIT_LOG_STATUS] ' . $e->getMessage());
                }
            });
        } catch (Throwable $e) {
            // La auditoría nunca debe interrumpir la operación principal.
            error_log('[AUDIT_LOG] ' . $e->getMessage());
        }
    }

    private static function describe(string $method, string $path, string $handler): array
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));
        $isApi = str_starts_with($path, '/api/');
        if (str_starts_with($path, '/api/admin/')) {\n            $modulePath = array_slice($segments, 2);\n        } elseif ($isApi) {\n            $modulePath = array_slice($segments, 1);\n        } else {\n            $modulePath = array_slice($segments, 1);\n        }
        $moduleKey = strtolower((string)($modulePath[0] ?? 'general'));
        $modules = [
            'dashboard' => 'Dashboard', 'reservations' => 'Reservas',
            'items' => 'Productos', 'categories' => 'Categorías',
            'coupons' => 'Cupones', 'pages' => 'Páginas públicas',
            'events' => 'Eventos', 'users' => 'Usuarios',
            'branches' => 'Sedes', 'settings' => 'Configuración',
            'profile' => 'Perfil', 'orders' => 'Pedidos',
            'payments' => 'Pagos', 'audit-logs' => 'Auditoría',\n            'metrics' => 'Indicadores', 'email' => 'Correo', 'notifications' => 'Notificaciones'
        ];
        $module = $modules[$moduleKey] ?? ucfirst(str_replace('-', ' ', $moduleKey));

        $actionKey = strtolower((string)(count($modulePath) > 1 ? end($modulePath) : 'index'));
        if (!$isApi && strtoupper($method) === 'GET' && in_array($actionKey, ['index', 'dashboard', ''], true)) {
            $action = 'Consulta de módulo';
        } elseif (str_contains($actionKey, 'login')) {
            $action = 'Inicio de sesión';
        } elseif (str_contains($actionKey, 'logout')) {
            $action = 'Cierre de sesión';
        } else {
            $actions = [
                'store' => 'Creación de registro', 'create' => 'Apertura de formulario',
                'update' => 'Actualización de registro', 'edit' => 'Edición de registro',
                'delete' => 'Eliminación de registro', 'destroy' => 'Eliminación de registro',
                'toggle' => 'Cambio de estado', 'status' => 'Cambio de estado',
                'save' => 'Guardado de información', 'show' => 'Consulta de detalle',
                'data' => 'Consulta de información', 'meta' => 'Consulta de configuración',
                'images' => 'Gestión de imágenes', 'activate' => 'Activación de registro'
            ];
            $action = $actions[$actionKey] ?? (strtoupper($method) === 'GET' ? 'Consulta' : 'Ejecución de acción');
        }

        if (strtoupper($method) === 'POST' && !$isApi) $action = 'Envío de formulario';
        if ($isApi && strtoupper($method) === 'GET' && $action === 'Consulta') $action = 'Consulta de información';
        return [$module, $action];
    }

    private static function clientIp(): ?string
    {
        $ip = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
    }
}
