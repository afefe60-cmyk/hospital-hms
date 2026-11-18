<?php

namespace App\Core;

class Request
{
    private string $method;
    private string $uri;
    private array $queryParams;
    private array $postData;
    private array $routeParams = [];
    private array $headers;
    private array $files;

    public function __construct()
    {
        $this->queryParams = $_GET;
        $this->postData = $_POST;
        $this->files = $_FILES;
        $this->headers = $this->extractHeaders();
        $this->uri = $this->parseUri();
        $this->method = $this->parseMethod();

        // Parse JSON payload if Content-Type is application/json
        if ($this->isJson()) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            if (is_array($data)) {
                $this->postData = array_merge($this->postData, $data);
            }
        }
    }

    private function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($uri, '?');
        if ($position !== false) {
            $uri = substr($uri, 0, $position);
        }
        $uri = rawurldecode($uri);
        return '/' . trim($uri, '/');
    }

    private function parseMethod(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST') {
            if (isset($_POST['_method'])) {
                $override = strtoupper((string)$_POST['_method']);
                if (in_array($override, ['PUT', 'DELETE', 'PATCH', 'OPTIONS'])) {
                    return $override;
                }
            }
            if (isset($this->headers['x-http-method-override'])) {
                return strtoupper((string)$this->headers['x-http-method-override']);
            }
        }
        return $method;
    }

    private function extractHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$headerName] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $headerName = strtolower(str_replace('_', '-', $key));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->method;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->postData[$key] ?? $this->queryParams[$key] ?? $this->routeParams[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->queryParams, $this->postData, $this->routeParams);
    }

    public function only(array $keys): array
    {
        $all = $this->all();
        $result = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $all)) {
                $result[$key] = $all[$key];
            }
        }
        return $result;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->queryParams;
        }
        return $this->queryParams[$key] ?? $default;
    }

    public function post(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->postData;
        }
        return $this->postData[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($this->postData[$key]) || isset($this->queryParams[$key]) || isset($this->routeParams[$key]);
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    public function isAjax(): bool
    {
        return strtolower((string)$this->header('x-requested-with')) === 'xmlhttprequest';
    }

    public function wantsJson(): bool
    {
        $accept = (string)$this->header('accept');
        return str_contains($accept, 'application/json') || $this->isAjax();
    }

    public function isJson(): bool
    {
        $contentType = (string)$this->header('content-type');
        return str_contains($contentType, 'application/json');
    }

    public function ip(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($list[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }
}
