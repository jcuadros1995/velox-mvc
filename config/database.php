<?php
// config/database.php

/**
 * Database connection.
 * Credentials must be injected by the deployment environment.
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    public static function getConnection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $host = self::env('DB_HOST', '127.0.0.1');
        $port = self::env('DB_PORT', '3306');
        $name = self::env('DB_NAME', 'velox_core');
        $user = self::env('DB_USER');
        $pass = self::env('DB_PASSWORD');
        $charset = 'utf8mb4';

        if ($user === '' || $pass === '') {
            error_log('[DATABASE] DB_USER/DB_PASSWORD are not configured.');
            throw new RuntimeException('Database configuration is unavailable.');
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

    private static function env(string $key, string $default = ''): string
    {
        $value = getenv($key);
        return $value === false ? $default : trim((string) $value);
    }
}
