<?php

namespace App\Core;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $lifetime = Config::get('session.lifetime', 7200);
        $secure = Config::get('session.secure', false);
        $cookieName = Config::get('session.cookie_name', 'hms_session');
        $samesite = Config::get('session.samesite', 'Lax');

        if (!headers_sent()) {
            session_name($cookieName);

            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => $samesite,
            ]);
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        self::$started = true;

        // Manage flash data age
        self::ageFlash();
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_unset();
            @session_destroy();
            self::$started = false;
        }
    }

    public static function regenerate(bool $deleteOldSession = true): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            return @session_regenerate_id($deleteOldSession);
        }
        return true;
    }

    /**
     * Flash messages (available for next request only)
     */
    public static function flash(string $key, mixed $value): void
    {
        self::start();
        $_SESSION['_flash']['new'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION['_flash']['old'][$key] ?? $_SESSION['_flash']['new'][$key] ?? $default;
    }

    public static function hasFlash(string $key): bool
    {
        self::start();
        return isset($_SESSION['_flash']['old'][$key]) || isset($_SESSION['_flash']['new'][$key]);
    }

    private static function ageFlash(): void
    {
        // Move current 'new' to 'old', remove expired 'old'
        if (isset($_SESSION['_flash']['old'])) {
            unset($_SESSION['_flash']['old']);
        }
        if (isset($_SESSION['_flash']['new'])) {
            $_SESSION['_flash']['old'] = $_SESSION['_flash']['new'];
            unset($_SESSION['_flash']['new']);
        }
    }

    /**
     * CSRF Token management
     */
    public static function token(): string
    {
        self::start();
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function validateToken(?string $token): bool
    {
        self::start();
        if (empty($token) || empty($_SESSION['_csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $token);
    }
}
