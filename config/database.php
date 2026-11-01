<?php
// config/database.php

/**
 * Database connection.
 *
 * Loads a project-local .env file when present. Real environment variables
 * always take precedence over values from .env.
 */
class Database
{
    private static ?PDO $instance = null;
    private static bool $environmentLoaded = false;

    private function __construct()
    {
    }

    public static function getConnection(): PDO
    {
        self::loadEnvironment();

        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $appEnv = strtolower(self::env('APP_ENV', 'production'));
        $host = self::env('DB_HOST', '127.0.0.1');
        $port = self::env('DB_PORT', '3306');
        $name = self::env('DB_NAME', 'velox_core');
        $user = self::env('DB_USER', 'root');
        $pass = self::env('DB_PASSWORD', '');
        $charset = 'utf8mb4';


        $isLoopback = in_array(strtolower($host), ['127.0.0.1', 'localhost', '::1'], true);
        $isLocalRootWithoutPassword = in_array($appEnv, ['local', 'development'], true)
            && $isLoopback
            && strtolower($user) === 'root'
            && $pass === '';

        if ($pass === '' && !$isLocalRootWithoutPassword) {
            error_log('[DATABASE] Empty DB_PASSWORD is only permitted for local root connections.');
            throw new RuntimeException('Database configuration is unavailable. Configure DB_PASSWORD.');
        }

        if (!preg_match('/^[a-zA-Z0-9_.:-]{1,255}$/', $host) ||
            !preg_match('/^[0-9]{1,5}$/', $port) ||
            (int) $port < 1 || (int) $port > 65535 ||
            !preg_match('/^[a-zA-Z0-9_]{1,64}$/', $name)) {
            throw new RuntimeException('Invalid database connection configuration.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $host,
            $port,
            $name,
            $charset
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_TIMEOUT => 5,
        ];

        self::$instance = new PDO($dsn, $user, $pass, $options);
        return self::$instance;
    }

    /**
     * Reads .env without a third-party dependency. Existing process
     * environment variables are never overwritten.
     */
    private static function loadEnvironment(): void
    {
        if (self::$environmentLoaded) {
            return;
        }

        self::$environmentLoaded = true;
        $envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

        if (!is_file($envFile) || !is_readable($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $key) || getenv($key) !== false) {
                continue;
            }

            // Remove matching surrounding quotes; preserve all other characters.
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = substr($value, -1);
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    private static function env(string $key, string $default = ''): string
    {
        $value = getenv($key);
        return $value === false ? $default : trim((string) $value);
    }
}
