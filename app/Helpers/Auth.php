<?php
// app/Helpers/Auth.php

class Auth
{
    private const REMEMBER_COOKIE = 'burgertown_remember';
    private const REMEMBER_DAYS = 30;
    private const REMEMBER_TOKEN_TYPE = 'remember_me';

    public static function init(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        $https = self::isHttps();
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', $https ? '1' : '0');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.cookie_lifetime', '0');

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => self::cookiePath(),
            'secure' => $https,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_start();
    }

    private static function isHttps(): bool
    {
        return (
            (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') ||
            (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        );
    }

    private static function cookiePath(): string
    {
        $path = parse_url(defined('APP_URL') ? APP_URL : '/', PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return '/';
        }
        return rtrim($path, '/') . '/';
    }

    public static function login(array $user): void
    {
        self::init();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_email'] = (string) ($user['email'] ?? '');
        $_SESSION['user_name'] = trim(
            (string) ($user['first_name'] ?? '') . ' ' . (string) ($user['last_name'] ?? '')
        );
        $_SESSION['role_id'] = (int) ($user['role_id'] ?? 0);
        $_SESSION['role_name'] = strtolower(trim((string) ($user['role_name'] ?? 'customer')));

        $roleName = $_SESSION['role_name'];
        $_SESSION['branch_id'] = $roleName === 'superadmin'
            ? null
            : (isset($user['branch_id']) && $user['branch_id'] !== null ? (int) $user['branch_id'] : null);
        $_SESSION['branch_name'] = $roleName === 'superadmin' ? null : ($user['branch_name'] ?? null);
    }

    public static function check(): bool
    {
        self::init();
        if (empty($_SESSION['user_id'])) {
            return false;
        }

        try {
            $securityService = new SecurityService();
            if (!$securityService->isSessionValid((int) $_SESSION['user_id'])) {
                self::logout();
                return false;
            }
            $securityService->refreshActivity();
            return true;
        } catch (Throwable $e) {
            error_log('[AUTH] Error validating session: ' . $e->getMessage());
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
            'id' => (int) $_SESSION['user_id'],
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
        $currentRole = strtolower(trim((string) ($_SESSION['role_name'] ?? '')));
        $allowedRoles = array_map(
            static fn($role) => strtolower(trim((string) $role)),
            $allowedRoles
        );
        return in_array($currentRole, $allowedRoles, true);
    }

    public static function logout(): void
    {
        self::init();
        self::clearRememberToken();

        $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
        if ($userId > 0) {
            try {
                (new SecurityService())->clearCurrentSession($userId);
            } catch (Throwable $e) {
                error_log('[AUTH_LOGOUT] ' . $e->getMessage());
            }
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'] ?: self::cookiePath(),
                'domain' => $params['domain'] ?? '',
                'secure' => (bool) ($params['secure'] ?? self::isHttps()),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function createRememberToken(int $userId): void
    {
        self::init();
        $db = Database::getConnection();

        $delete = $db->prepare(
            'DELETE FROM user_tokens WHERE user_id = :user_id AND type = :type'
        );
        $delete->execute(['user_id' => $userId, 'type' => self::REMEMBER_TOKEN_TYPE]);

        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);
        $expiresAt = date('Y-m-d H:i:s', time() + self::REMEMBER_DAYS * 86400);

        $insert = $db->prepare(
            'INSERT INTO user_tokens (user_id, token_hash, type, expires_at)
             VALUES (:user_id, :token_hash, :type, :expires_at)'
        );
        $insert->execute([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'type' => self::REMEMBER_TOKEN_TYPE,
            'expires_at' => $expiresAt
        ]);

        setcookie(self::REMEMBER_COOKIE, $plainToken, [
            'expires' => time() + self::REMEMBER_DAYS * 86400,
            'path' => self::cookiePath(),
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    public static function clearRememberToken(): void
    {
        self::init();
        $plainToken = (string) ($_COOKIE[self::REMEMBER_COOKIE] ?? '');

        if ($plainToken !== '' && preg_match('/^[a-f0-9]{64}$/i', $plainToken)) {
            try {
                $delete = Database::getConnection()->prepare(
                    'DELETE FROM user_tokens WHERE token_hash = :token_hash AND type = :type'
                );
                $delete->execute([
                    'token_hash' => hash('sha256', $plainToken),
                    'type' => self::REMEMBER_TOKEN_TYPE
                ]);
            } catch (Throwable $e) {
                error_log('[AUTH_REMEMBER_CLEAR] ' . $e->getMessage());
            }
        }

        setcookie(self::REMEMBER_COOKIE, '', [
            'expires' => time() - 3600,
            'path' => self::cookiePath(),
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        unset($_COOKIE[self::REMEMBER_COOKIE]);
    }

    public static function attemptRemember(): bool
    {
        self::init();
        if (!empty($_SESSION['user_id'])) {
            return true;
        }

        $plainToken = (string) ($_COOKIE[self::REMEMBER_COOKIE] ?? '');
        if (!preg_match('/^[a-f0-9]{64}$/i', $plainToken)) {
            if ($plainToken !== '') {
                self::clearRememberToken();
            }
            return false;
        }

        try {
            $stmt = Database::getConnection()->prepare(
                "SELECT u.*, r.name AS role_name, b.name AS branch_name
                 FROM user_tokens ut
                 INNER JOIN users u ON u.id = ut.user_id
                 INNER JOIN roles r ON r.id = u.role_id
                 LEFT JOIN branches b ON b.id = u.branch_id
                 WHERE ut.token_hash = :token_hash
                   AND ut.type = :type
                   AND ut.expires_at > NOW()
                   AND u.is_active = 1
                 LIMIT 1"
            );
            $stmt->execute([
                'token_hash' => hash('sha256', $plainToken),
                'type' => self::REMEMBER_TOKEN_TYPE
            ]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                self::clearRememberToken();
                return false;
            }

            self::login($user);
            $securityService = new SecurityService();
            $securityService->initializeSession((int) $user['id']);
            $_SESSION['password_expired'] = $securityService->isPasswordExpired($user);

            // Rotate the persistent credential every time it is consumed.
            self::createRememberToken((int) $user['id']);
            return true;
        } catch (Throwable $e) {
            error_log('[AUTH_REMEMBER] ' . $e->getMessage());
            self::clearRememberToken();
            return false;
        }
    }
}
