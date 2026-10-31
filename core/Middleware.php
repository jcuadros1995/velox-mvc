<?php
// core/Middleware.php

class Middleware
{
    /**
     * Autenticación para APIs.
     *
     * Si no existe una sesión PHP válida,
     * intenta recuperar la sesión mediante
     * el remember token.
     *
     * Si tampoco existe un remember token válido,
     * devuelve una respuesta JSON 401.
     */
    public static function auth(): bool
    {
        if (!Auth::check()) {

            Auth::attemptRemember();
        }


        if (!Auth::check()) {

            http_response_code(401);

            header(
                'Content-Type: application/json; charset=utf-8'
            );

            echo json_encode([
                'success' => false,
                'message' =>
                    'Sesión expirada o no autorizada.'
            ]);

            return false;
        }


        return true;
    }


    /**
     * Autenticación para páginas web.
     *
     * Si no existe una sesión PHP válida,
     * intenta recuperar la sesión mediante
     * el remember token.
     *
     * Si tampoco existe un token válido,
     * redirige al login.
     */
    public static function webAuth(): bool
    {
        if (!Auth::check()) {

            Auth::attemptRemember();
        }


        if (!Auth::check()) {

            Auth::init();

            $_SESSION['toast_error'] =
                'Debes iniciar sesión para acceder a este apartado.';


            header(
                'Location: ' .
                APP_URL .
                '/login'
            );

            return false;
        }


        return true;
    }


    /**
     * Acceso para usuarios invitados.
     *
     * Si el usuario ya tiene una sesión activa
     * o puede recuperarla mediante remember me,
     * lo redirige directamente al dashboard.
     */
    public static function webGuest(): bool
    {
        if (!Auth::check()) {
            Auth::attemptRemember();
        }

        $user = Auth::user();

        if ($user) {
            $role = strtolower(
                trim((string) ($user['role_name'] ?? ''))
            );

            $destination = $role === 'customer'
                ? '/'
                : '/admin/dashboard';

            header(
                'Location: ' .
                rtrim(APP_URL, '/') .
                $destination
            );

            exit;
        }

        return true;
    }


    /**
     * Autorización por roles para APIs.
     */
    public static function role(array $roles): bool
    {
        if (!self::auth()) {
            return false;
        }


        if (!Auth::hasRole($roles)) {

            http_response_code(403);

            header(
                'Content-Type: application/json; charset=utf-8'
            );

            echo json_encode([
                'success' => false,
                'message' =>
                    'No tiene permisos suficientes para esta acción.'
            ]);

            return false;
        }


        return true;
    }


    /**
     * Autorización por roles para páginas web.
     */
    public static function webRole(array $roles): bool
    {
        if (!self::webAuth()) {
            return false;
        }

        if (Auth::hasRole($roles)) {
            return true;
        }

        http_response_code(403);

        /*
         * Ruta absoluta al proyecto
         */
        $basePath = dirname(__DIR__);

        /*
         * Ruta absoluta de la vista 403
         */
        $contentView = $basePath . DIRECTORY_SEPARATOR .
            'app' . DIRECTORY_SEPARATOR .
            'Views' . DIRECTORY_SEPARATOR .
            'errors' . DIRECTORY_SEPARATOR .
            '403.php';

        $title = 'Acceso restringido | Burger Town';

        $isAdminError = true;



        /*
         * Comprobación temporal
         */
        if (!file_exists($contentView)) {

            die(
                '<h2>Error interno</h2>' .
                '<p>No se encontró la vista 403.</p>' .
                '<pre>' .
                htmlspecialchars($contentView) .
                '</pre>'
            );
        }

        require $basePath .
            DIRECTORY_SEPARATOR .
            'app' .
            DIRECTORY_SEPARATOR .
            'Views' .
            DIRECTORY_SEPARATOR .
            'admin' .
            DIRECTORY_SEPARATOR .
            'layout.php';

        return false;
    }

    /**
     * Comprueba permisos efectivos (rol + excepciones individuales).
     * Los permisos de user_permissions prevalecen sobre los del rol.
     */
    private static function userHasPermission(string $permission): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $role = strtolower(trim((string)($user['role_name'] ?? '')));

        // Mantener el acceso administrativo global existente.
        if (in_array($role, ['superadmin', 'admin'], true)) {
            return true;
        }

        try {
            $repository = new UserRepository();
            $rolePermissions = $repository->getPermissionsByRoleId(
                isset($user['role_id']) ? (int)$user['role_id'] : null
            );
            $effective = array_fill_keys($rolePermissions, true);

            foreach ($repository->getUserPermissionOverrides((int)$user['id']) as $override) {
                $slug = (string)($override['slug'] ?? '');
                if ($slug === '') {
                    continue;
                }
                if ((int)($override['is_allowed'] ?? 0) === 1) {
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

    /** API: permite acceso si el usuario tiene al menos uno de los permisos. */
    public static function anyPermission(array $permissions): bool
    {
        if (!self::auth()) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (self::userHasPermission((string)$permission)) {
                return true;
            }
        }

        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'No tiene permisos para realizar esta acción.'
        ]);
        return false;
    }

    /** Página web: permite acceso si el usuario tiene al menos uno de los permisos. */
    public static function webAnyPermission(array $permissions): bool
    {
        if (!self::webAuth()) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (self::userHasPermission((string)$permission)) {
                return true;
            }
        }

        http_response_code(403);
        $basePath = dirname(__DIR__);
        $contentView = $basePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . '403.php';
        $title = 'Acceso restringido | Burger Town';
        $isAdminError = true;

        if (file_exists($contentView)) {
            require $basePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'layout.php';
        } else {
            echo '403 - No tiene permisos para acceder a esta sección.';
        }
        return false;
    }

}