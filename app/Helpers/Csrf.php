<?php

/**
 * CSRF token helper for HTML forms and fetch/AJAX requests.
 */
class Csrf
{
    public static function token(): string
    {
        Auth::init();
        if (empty($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' .
            htmlspecialchars(self::token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
            '">';
    }

    public static function meta(): string
    {
        return '<meta name="csrf-token" content="' .
            htmlspecialchars(self::token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
            '">';
    }
}
