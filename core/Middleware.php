<?php

/**
 * Security and authorization middleware.
 */
class Middleware
{
    public static function auth(): bool
    {
        if (!Auth::check()) {
            Auth::attemptRemember();
        }

        if (!Auth::check()) {
            self::jsonError(401, 'Sesión expirada o no autorizada.');
            return false;
        }

        return true;
    }

    public static function webAuth(): bool
    {
        if (!Auth::check()) {
            Auth::attemptRemember();
        }

        if (!Auth::check()) {
            Auth::init();
            $_SESSION['toast_error'] = 'Debes iniciar sesión para acceder a este apartado.';
            header('Location: ' . rtrim(APP_URL, '/') . '/login', true, 302);
            return false;
        }

        return true;
    }

    public static function webGuest(): bool
    {
        if (!Auth::check()) {
            Auth::attemptRemember();
        }

        $user = Auth::user();
        if ($user) {
            $role = strtolower(trim((string) ($user['role_name'] ?? '')));
            $destination = $role === 'customer' ? '/' : '/admin/dashboard';
            header('Location: ' . rtrim(APP_URL, '/') . $destination, true, 302);
            exit;
        }

        return true;
    }

    public static function role(array $roles): bool
    {
        if (!self::auth()) {
            return false;
        }

        if (!Auth::hasRole($roles)) {
            self::jsonError(403, 'No tiene permisos suficientes para esta acción.');
            return false;
        }

        return true;
    }

    public static function webRole(array $roles): bool
    {
        if (!self::webAuth()) {
            return false;
        }

        if (Auth::hasRole($roles)) {
            return true;
        }

        http_response_code(403);
        $basePath = dirname(__DIR__);
        $contentView = $basePath . '/app/Views/errors/403.php';
        $title = 'Acceso restringido | Burger Town';
        $isAdminError = true;

        if (is_file($contentView)) {
            require $basePath . '/app/Views/admin/layout.php';
        } else {
            echo '403 - No tiene permisos para acceder a esta sección.';
        }
        return false;
    }

    private static function userHasPermission(string $permission): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $role = strtolower(trim((string) ($user['role_name'] ?? '')));
        if (in_array($role, ['superadmin', 'admin'], true)) {
            return true;
        }

        try {
            $repository = new UserRepository();
            $rolePermissions = $repository->getPermissionsByRoleId(
                isset($user['role_id']) ? (int) $user['role_id'] : null
            );
            $effective = array_fill_keys($rolePermissions, true);

            foreach ($repository->getUserPermissionOverrides((int) $user['id']) as $override) {
                $slug = (string) ($override['slug'] ?? '');
                if ($slug === '') {
                    continue;
                }
                if ((int) ($override['is_allowed'] ?? 0) === 1) {
                    $effective[$slug] = true;
                } else {
                    unset($effective[$slug]);
                }
            }

            return isset($effective[$permission]);
        } catch (Throwable $e) {
            error_log('[PERMISSION_CHECK] ' . $e->getMessage());
            return false;
        }
    }

    public static function canAnyPermission(array $permissions): bool
    {
        if (!Auth::check()) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (self::userHasPermission((string) $permission)) {
                return true;
            }
        }

        return false;
    }

    public static function anyPermission(array $permissions): bool
    {
        if (!self::auth()) {
            return false;
        }

        if (self::canAnyPermission($permissions)) {
            return true;
        }

        self::jsonError(403, 'No tiene permisos para realizar esta acción.');
        return false;
    }

    public static function webAnyPermission(array $permissions): bool
    {
        if (!self::webAuth()) {
            return false;
        }

        if (self::canAnyPermission($permissions)) {
            return true;
        }

        http_response_code(403);
        $basePath = dirname(__DIR__);
        $contentView = $basePath . '/app/Views/errors/403.php';
        $title = 'Acceso restringido | Burger Town';
        $isAdminError = true;

        if (is_file($contentView)) {
            require $basePath . '/app/Views/admin/layout.php';
        } else {
            echo '403 - No tiene permisos para acceder a esta sección.';
        }
        return false;
    }

    /**
     * CSRF protection for cookie-authenticated mutations.
     * Safe methods are never checked here. Signed Wompi callbacks must
     * validate their own event signature in the payment adapter.
     */
    public static function csrf(): bool
    {
        if (!in_array(strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return true;
        }

        $expected = (string) ($_SESSION['_csrf_token'] ?? '');
        $provided = (string) (
            $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? $_POST['_csrf_token']
            ?? ''
        );

        if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
            self::jsonError(419, 'La solicitud expiró o no es válida. Actualiza la página e inténtalo nuevamente.');
            return false;
        }

        return true;
    }

    public static function jsonError(int $status, string $message): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
