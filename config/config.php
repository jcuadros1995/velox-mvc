<?php
// config/config.php

date_default_timezone_set('America/Bogota');

/**
 * Canonical application URL.
 *
 * Never build password-reset or email links from HTTP_HOST: that header is
 * client-controlled and can enable Host-header poisoning. Configure APP_URL
 * in the server environment for production. The local fallback uses the
 * web-server configured SERVER_NAME instead.
 */
if (getenv('APP_URL') === false) {
    $envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    if (is_file($envFile) && is_readable($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES) ?: [] as $envLine) {
            $envLine = trim($envLine);
            if (strpos($envLine, 'APP_URL=') !== 0) {
                continue;
            }
            $envValue = trim(substr($envLine, 8));
            if (strlen($envValue) >= 2) {
                $first = $envValue[0];
                $last = substr($envValue, -1);
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $envValue = substr($envValue, 1, -1);
                }
            }
            putenv('APP_URL=' . $envValue);
            break;
        }
    }
}

$configuredAppUrl = trim((string) (getenv('APP_URL') ?: ''));

if ($configuredAppUrl !== '') {
    $parts = parse_url($configuredAppUrl);
    $validUrl = filter_var($configuredAppUrl, FILTER_VALIDATE_URL) !== false
        && is_array($parts)
        && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
        && !empty($parts['host'])
        && !isset($parts['user'])
        && !isset($parts['pass'])
        && !isset($parts['query'])
        && !isset($parts['fragment']);

    if (!$validUrl) {
        throw new RuntimeException('APP_URL must be a valid canonical HTTP(S) URL without credentials, query or fragment.');
    }

    $baseUrl = rtrim($configuredAppUrl, '/');
} else {
    $serverName = trim((string) ($_SERVER['SERVER_NAME'] ?? 'localhost'));
    if (
        $serverName === '' ||
        !preg_match('/^(?:[a-z0-9](?:[a-z0-9.-]*[a-z0-9])?|\[[0-9a-f:]+\])$/i', $serverName)
    ) {
        throw new RuntimeException('Invalid server name. Configure APP_URL explicitly.');
    }

    $isHttps = (
        (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') ||
        (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
    );
    $protocol = $isHttps ? 'https://' : 'http://';
    $port = isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 0;
    $portSuffix = ($port > 0 && !(($isHttps && $port === 443) || (!$isHttps && $port === 80)))
        ? ':' . $port
        : '';

    $scriptName = str_replace('\\\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $scriptDirectory = str_replace('\\\\', '/', dirname($scriptName));
    $basePath = ($scriptDirectory === '/' || $scriptDirectory === '.') ? '' : rtrim($scriptDirectory, '/');
    $baseUrl = $protocol . $serverName . $portSuffix . $basePath;
    $baseUrl = preg_replace('/\\/public$/', '', $baseUrl);
}

define('APP_URL', $baseUrl);
