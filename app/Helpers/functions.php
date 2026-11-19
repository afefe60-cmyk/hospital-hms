<?php

use App\Core\App;
use App\Core\Config;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

if (!function_exists('app')) {
    function app(): ?App
    {
        return App::getInstance();
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('view')) {
    function view(string $view, array $data = [], ?string $layout = 'layouts/main'): string
    {
        return View::render($view, $data, $layout);
    }
}

if (!function_exists('response')) {
    function response(): Response
    {
        return new Response();
    }
}

if (!function_exists('json')) {
    function json(mixed $data, int $statusCode = 200, array $headers = []): Response
    {
        $res = new Response();
        return $res->json($data, $statusCode, $headers);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $statusCode = 302): void
    {
        $res = new Response();
        $res->redirect($url, $statusCode);
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('__')) {
    function __(string $key, array $replace = [], ?string $locale = null): string
    {
        return View::trans($key, $replace, $locale);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Session::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = null): mixed
    {
        $old = Session::getFlash('_old_input', []);
        return $old[$key] ?? $default;
    }
}

if (!function_exists('session')) {
    function session(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return new Session();
        }
        return Session::get($key, $default);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                       (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                       (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
            $scheme = $isHttps ? 'https' : 'http';
            $base = $scheme . '://' . $_SERVER['HTTP_HOST'];
        } else {
            $base = rtrim(config('app.url', 'http://localhost:8080'), '/');
        }
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('format_currency')) {
    function format_currency(float|int|string $amount, string $currency = 'SDG'): string
    {
        $num = (float)$amount;
        $formatted = number_format($num, 2, '.', ',');
        $locale = Session::get('locale', config('app.locale', 'ar'));

        if ($locale === 'ar') {
            return "{$formatted} ج.س";
        }
        return "{$formatted} SDG";
    }
}

if (!function_exists('active_route')) {
    function active_route(string $uri, string $activeClass = 'active'): string
    {
        $currentUri = rawurldecode($_SERVER['REQUEST_URI'] ?? '/');
        $position = strpos($currentUri, '?');
        if ($position !== false) {
            $currentUri = substr($currentUri, 0, $position);
        }
        $currentUri = '/' . trim($currentUri, '/');
        $targetUri = '/' . trim($uri, '/');

        if ($targetUri === '/' && $currentUri === '/') {
            return $activeClass;
        }

        if ($targetUri !== '/' && str_starts_with($currentUri, $targetUri)) {
            return $activeClass;
        }

        return '';
    }
}

if (!function_exists('active_group')) {
    function active_group(array $uris, string $activeClass = 'active'): string
    {
        foreach ($uris as $u) {
            if (active_route($u) !== '') {
                return $activeClass;
            }
        }
        return '';
    }
}

if (!function_exists('auth')) {
    function auth(): \App\Services\AuthService
    {
        static $authService = null;
        if ($authService === null) {
            $authService = new \App\Services\AuthService();
        }
        return $authService;
    }
}

if (!function_exists('can')) {
    function can(string $permission): bool
    {
        return auth()->can($permission);
    }
}

if (!function_exists('has_role')) {
    function has_role(string $role): bool
    {
        return auth()->hasRole($role);
    }
}

if (!function_exists('current_user')) {
    function current_user(): ?array
    {
        return auth()->user();
    }
}
