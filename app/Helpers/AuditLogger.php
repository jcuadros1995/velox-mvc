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

            // Para cambios de configuración guardamos únicamente los nombres
            // de los campos enviados; nunca sus valores ni archivos/credenciales.
            if (
                in_array($module, ['Configuración general', 'Configuración de sedes'], true) &&
                strtoupper($method) !== 'GET'
            ) {
                $fieldNames = array_merge(
                    array_keys($_POST ?? []),
                    array_keys($_FILES ?? [])
                );
                $safeFields = [];
                foreach ($fieldNames as $fieldName) {
                    $fieldName = preg_replace('/[^a-zA-Z0-9_.-]/', '', (string)$fieldName);
                    if (
                        $fieldName === '' ||
                        preg_match('/password|passwd|secret|token|credential|private|api.?key|csrf/i', $fieldName)
                    ) {
                        continue;
                    }
                    $safeFields[] = $fieldName;
                }
                $safeFields = array_slice(array_values(array_unique($safeFields)), 0, 5);
                if ($safeFields) {
                    $action .= ' · Campos: ' . implode(', ', $safeFields);
                }
                $action = substr($action, 0, 160);
            }

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
        if (str_starts_with($path, '/api/admin/')) {
            $modulePath = array_slice($segments, 2);
        } elseif ($isApi) {
            $modulePath = array_slice($segments, 1);
        } else {
            $modulePath = array_slice($segments, 1);
        }
        $moduleKey = strtolower((string)($modulePath[0] ?? 'general'));
        $modules = [
            'dashboard' => 'Dashboard', 'reservations' => 'Reservas',
            'items' => 'Productos', 'categories' => 'Categorías',
            'coupons' => 'Cupones', 'pages' => 'Páginas públicas',
            'events' => 'Eventos', 'users' => 'Usuarios',
            'branches' => 'Sedes', 'settings' => 'Configuración',
            'profile' => 'Perfil', 'orders' => 'Pedidos',
            'payments' => 'Pagos', 'audit-logs' => 'Auditoría',
            'metrics' => 'Indicadores', 'email' => 'Correo', 'notifications' => 'Notificaciones'
        ];
        $module = $modules[$moduleKey] ?? ucfirst(str_replace('-', ' ', $moduleKey));

        // Diferenciar configuración general y configuración operativa de sedes.
        if ($moduleKey === 'settings') {
            $module = 'Configuración general';
        } elseif ($moduleKey === 'branches') {
            $module = 'Configuración de sedes';
        }

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

        // Nombrar explícitamente las operaciones sensibles de configuración.
        if ($moduleKey === 'settings') {
            $action = strtoupper($method) === 'POST'
                ? 'Actualización de configuración general'
                : 'Consulta de configuración general';
        } elseif ($moduleKey === 'branches') {
            $branchActions = [
                'index' => 'Consulta de configuración de sedes',
                'store' => 'Creación de sede',
                'update' => 'Actualización de datos de sede',
                'save-hours' => 'Actualización de horarios de sede',
                'images' => 'Gestión de imágenes de sede',
                'save' => 'Actualización de imágenes y recursos',
                'create' => 'Creación de mesa',
                'update' => 'Actualización de sede o mesa',
                'delete' => 'Eliminación de sede o mesa',
                'delete-permanently' => 'Eliminación permanente de mesa',
                'show' => 'Consulta de configuración de sede',
                'tables' => 'Consulta de mesas de sede'
            ];
            $action = $branchActions[$actionKey] ?? (
                strtoupper($method) === 'GET'
                    ? 'Consulta de configuración de sede'
                    : 'Actualización de configuración de sede'
            );
        }

        return [$module, $action];
    }

    /**
     * Registra eventos públicos de autenticación y recuperación.
     * No recibe ni almacena contraseñas, tokens ni códigos de recuperación.
     */
    public static function recordSecurityEvent(
        string $action,
        string $identifier = '',
        ?array $authenticatedUser = null,
        int $status = 200,
        string $route = ''
    ): void {
        try {
            $user = $authenticatedUser ?? (Auth::user() ?? []);
            $userId = isset($user['id']) ? (int)$user['id'] : null;
            $email = trim((string)($user['email'] ?? ''));
            $safeIdentifier = self::maskIdentifier($identifier);

            if ($email === '' && filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $email = strtolower(trim($identifier));
            }

            $name = trim((string)(
                $user['name']
                ?? $user['full_name']
                ?? trim((string)($user['first_name'] ?? '') . ' ' . (string)($user['last_name'] ?? ''))
            ));
            if ($name === '') {
                $name = $safeIdentifier !== '' ? $safeIdentifier : 'Visitante';
            }

            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO admin_audit_logs
                    (user_id, user_name, user_email, user_role, branch_id,
                     module, action, http_method, route, ip_address, user_agent,
                     response_status, created_at)
                VALUES
                    (:user_id, :user_name, :user_email, :user_role, :branch_id,
                     'Autenticación y seguridad', :action, :http_method, :route,
                     :ip_address, :user_agent, :response_status, NOW())
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':user_name' => $name,
                ':user_email' => $email !== '' ? $email : null,
                ':user_role' => isset($user['role_name']) ? (string)$user['role_name'] : null,
                ':branch_id' => isset($user['branch_id']) ? (int)$user['branch_id'] : null,
                ':action' => substr($action . ($safeIdentifier !== '' ? ' · Identificador: ' . $safeIdentifier : ''), 0, 160),
                ':http_method' => strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'POST')),
                ':route' => substr($route !== '' ? $route : (string)(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/'), 0, 255),
                ':ip_address' => self::clientIp(),
                ':user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
                ':response_status' => $status
            ]);
        } catch (Throwable $e) {
            error_log('[AUTH_AUDIT_LOG] ' . $e->getMessage());
        }
    }

    private static function maskIdentifier(string $identifier): string
    {
        $identifier = trim($identifier);
        if ($identifier === '') return '';

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            [$local, $domain] = explode('@', $identifier, 2);
            $visible = substr($local, 0, 1);
            return $visible . str_repeat('*', max(2, strlen($local) - 1)) . '@' . $domain;
        }

        $digits = preg_replace('/\D+/', '', $identifier);
        if ($digits !== '' && strlen($digits) >= 4) {
            return '••••' . substr($digits, -4);
        }

        return substr($identifier, 0, 2) . '***';
    }

    private static function clientIp(): ?string
    {
        $ip = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
    }
}
