<?php
// app/Helpers/Auth.php

class Auth
{

    private const REMEMBER_COOKIE =
        'burgertown_remember';

    private const REMEMBER_DAYS =
        30;

    private const REMEMBER_TOKEN_TYPE = 'remember_me';

    public static function init(): void
    {
        if (
            session_status() !==
            PHP_SESSION_NONE
        ) {
            return;
        }

        ini_set(
            'session.use_strict_mode',
            '1'
        );

        ini_set(
            'session.use_only_cookies',
            '1'
        );

        ini_set(
            'session.cookie_httponly',
            '1'
        );

        ini_set(
            'session.cookie_samesite',
            'Lax'
        );

        session_start();
    }

    public static function login(array $user): void
    {
        self::init();

        // Evita session fixation
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = trim(
            ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')
        );

        $_SESSION['role_id'] = (int) $user['role_id'];

        // Normalizamos el rol para evitar problemas
        // entre "SuperAdmin" y "superadmin"
        $_SESSION['role_name'] = strtolower(
            trim($user['role_name'] ?? 'customer')
        );

        $roleName = strtolower(trim($user['role_name'] ?? 'customer'));

        // SuperAdmin tiene alcance global, sin sede fija.
        $_SESSION['branch_id'] = $roleName === 'superadmin'
            ? null
            : (isset($user['branch_id']) && $user['branch_id'] !== null
                ? (int) $user['branch_id']
                : null);

        $_SESSION['branch_name'] =
            $roleName === 'superadmin'
                ? null
                : ($user['branch_name'] ?? null);
    }

    public static function check(): bool
    {
        self::init();

        if (
            !isset($_SESSION['user_id']) ||
            empty($_SESSION['user_id'])
        ) {
            return false;
        }

        $userId =
            (int) $_SESSION['user_id'];

        try {

            $securityService =
                new SecurityService();

            if (
                !$securityService
                    ->isSessionValid(
                        $userId
                    )
            ) {

                self::logout();

                return false;
            }

            $securityService
                ->refreshActivity();

            return true;

        } catch (Throwable $e) {

            error_log(
                '[AUTH] Error validando sesión: ' .
                $e->getMessage()
            );

            self::logout();

            return false;
        }
    }

    public static function user(): ?array
    {
        self::init();

        if (!self::check()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'role_id' => $_SESSION['role_id'] ?? null,
            'role_name' => $_SESSION['role_name'] ?? null,
            'branch_id' => $_SESSION['branch_id'] ?? null,
            'branch_name' => $_SESSION['branch_name'] ?? null
        ];
    }

    public static function hasRole(array $allowedRoles): bool
    {
        if (!self::check()) {
            return false;
        }

        $currentRole = strtolower(
            trim($_SESSION['role_name'] ?? '')
        );

        $allowedRoles = array_map(
            fn($role) => strtolower(trim($role)),
            $allowedRoles
        );

        return in_array($currentRole, $allowedRoles, true);
    }

    public static function logout(): void
    {
        self::init();

        self::clearRememberToken();

        /*
         * Intentar limpiar únicamente la sesión
         * que actualmente pertenece a este navegador.
         */
        $userId =
            isset($_SESSION['user_id'])
                ? (int) $_SESSION['user_id']
                : 0;

        if ($userId > 0) {

            try {

                $securityService =
                    new SecurityService();

                $securityService
                    ->clearCurrentSession(
                        $userId
                    );

            } catch (Throwable $e) {

                error_log(
                    '[AUTH_LOGOUT] ' .
                    $e->getMessage()
                );
            }
        }


        $_SESSION = [];


        if (
            ini_get('session.use_cookies')
        ) {

            $params =
                session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }


        session_destroy();
    }

    /**
     * Crea un token persistente para "Mantener mi sesión iniciada".
     *
     * El token plano solamente se envía al navegador.
     * En la BD se almacena únicamente su SHA-256.
     */
    public static function createRememberToken(int $userId): void
    {
        self::init();

        $db = Database::getConnection();


        /*
         * Eliminar tokens remember anteriores
         * del usuario.
         */
        $delete = $db->prepare("
        DELETE FROM user_tokens
        WHERE user_id = :user_id
          AND type = :type
    ");

        $delete->execute([
            'user_id' => $userId,
            'type'    => self::REMEMBER_TOKEN_TYPE
        ]);


        /*
         * Generar token criptográficamente seguro.
         */
        $plainToken = bin2hex(
            random_bytes(32)
        );


        /*
         * Nunca guardar el token original
         * en la base de datos.
         */
        $tokenHash = hash(
            'sha256',
            $plainToken
        );


        /*
         * Expira en 30 días.
         */
        $expiresAt = date(
            'Y-m-d H:i:s',
            time() + (
                self::REMEMBER_DAYS * 86400
            )
        );


        /*
         * Guardar token.
         */
        $insert = $db->prepare("
        INSERT INTO user_tokens
        (
            user_id,
            token_hash,
            type,
            expires_at
        )
        VALUES
        (
            :user_id,
            :token_hash,
            :type,
            :expires_at
        )
    ");

        $insert->execute([
            'user_id'    => $userId,
            'token_hash' => $tokenHash,
            'type'       => self::REMEMBER_TOKEN_TYPE,
            'expires_at' => $expiresAt
        ]);


        /*
         * Guardar el token original en una cookie
         * persistente y protegida.
         */
        setcookie(
            self::REMEMBER_COOKIE,
            $plainToken,
            [
                'expires' => time() + (
                        self::REMEMBER_DAYS * 86400
                    ),

                'path' => '/',

                'secure' => (
                    !empty($_SERVER['HTTPS']) &&
                    $_SERVER['HTTPS'] !== 'off'
                ),

                'httponly' => true,

                'samesite' => 'Lax'
            ]
        );
    }

    /**
     * Elimina el remember token actual.
     */
    public static function clearRememberToken(): void
    {
        self::init();


        $plainToken =
            $_COOKIE[
            self::REMEMBER_COOKIE
            ] ?? '';


        if ($plainToken !== '') {

            $tokenHash = hash(
                'sha256',
                $plainToken
            );


            $db =
                Database::getConnection();


            $delete =
                $db->prepare("
                DELETE FROM user_tokens

                WHERE token_hash = :token_hash

                  AND type = :type
            ");


            $delete->execute([
                'token_hash' =>
                    $tokenHash,

                'type' =>
                    self::REMEMBER_TOKEN_TYPE
            ]);
        }


        /*
         * Eliminar cookie del navegador.
         */
        setcookie(
            self::REMEMBER_COOKIE,
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',

                'secure' => (
                    !empty($_SERVER['HTTPS']) &&
                    $_SERVER['HTTPS'] !== 'off'
                ),

                'httponly' => true,

                'samesite' => 'Lax'
            ]
        );
    }

    /**
     * Intenta reconstruir la sesión utilizando
     * el remember token almacenado en cookie.
     */
    public static function attemptRemember(): bool
    {
        self::init();


        /*
         * Si ya hay sesión, no hacemos nada.
         */
        if (
            !empty($_SESSION['user_id'])
        ) {
            return true;
        }


        /*
         * Leer cookie.
         */
        $plainToken =
            $_COOKIE[
            self::REMEMBER_COOKIE
            ] ?? '';


        if ($plainToken === '') {
            return false;
        }


        /*
         * Convertir token a hash.
         */
        $tokenHash = hash(
            'sha256',
            $plainToken
        );


        $db =
            Database::getConnection();


        /*
         * Buscar token válido.
         */
        $stmt = $db->prepare("
            SELECT
                u.*,
                r.name AS role_name,
                b.name AS branch_name
        
            FROM user_tokens ut
        
            INNER JOIN users u
                ON u.id = ut.user_id
        
            INNER JOIN roles r
                ON r.id = u.role_id
        
            LEFT JOIN branches b
                ON b.id = u.branch_id
        
            WHERE ut.token_hash = :token_hash
        
              AND ut.type = :type
        
              AND ut.expires_at > NOW()
        
              AND u.is_active = 1
        
            LIMIT 1
        ");


        $stmt->execute([

            'token_hash' =>
                $tokenHash,

            'type' =>
                self::REMEMBER_TOKEN_TYPE

        ]);


        $user =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );


        /*
         * Token inválido o expirado.
         */
        if (!$user) {

            self::clearRememberToken();

            return false;
        }


        /*
         * Crear una nueva sesión.
         */
        session_regenerate_id(true);


        /*
         * Utilizar exactamente el mismo
         * mecanismo de login existente.
         */
        self::login($user);

        $securityService =
            new SecurityService();

        $securityService->initializeSession(
            (int) $user['id']
        );

        /*
         * Guardar estado de expiración
         * de contraseña.
         */
        $_SESSION['password_expired'] =
            $securityService->isPasswordExpired(
                $user
            );


        /*
         * Rotar el remember token.
         *
         * Esto evita reutilizar indefinidamente
         * el mismo token.
         */
        self::createRememberToken(
            (int) $user['id']
        );


        return true;
    }
}