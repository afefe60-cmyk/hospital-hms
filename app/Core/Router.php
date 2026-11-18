<?php

namespace App\Core;

use RuntimeException;

class Router
{
    private array $routes = [];
    private array $groupStack = [];

    public function get(string $uri, mixed $action, array $middleware = []): self
    {
        return $this->addRoute('GET', $uri, $action, $middleware);
    }

    public function post(string $uri, mixed $action, array $middleware = []): self
    {
        return $this->addRoute('POST', $uri, $action, $middleware);
    }

    public function put(string $uri, mixed $action, array $middleware = []): self
    {
        return $this->addRoute('PUT', $uri, $action, $middleware);
    }

    public function delete(string $uri, mixed $action, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $uri, $action, $middleware);
    }

    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    private function addRoute(string $method, string $uri, mixed $action, array $middleware = []): self
    {
        $prefix = '';
        $groupMiddleware = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $groupMiddleware = array_merge($groupMiddleware, (array)$group['middleware']);
            }
        }

        $fullUri = '/' . trim($prefix . '/' . trim($uri, '/'), '/');
        if ($fullUri !== '/') {
            $fullUri = rtrim($fullUri, '/');
        }

        $allMiddleware = array_merge($groupMiddleware, $middleware);

        $this->routes[] = [
            'method' => strtoupper($method),
            'uri' => $fullUri,
            'action' => $action,
            'middleware' => $allMiddleware,
            'pattern' => $this->compilePattern($fullUri),
        ];

        return $this;
    }

    private function compilePattern(string $uri): string
    {
        // Replace {param} with regex capture group named param: (?P<param>[^/]+)
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(Request $request, Response $response): mixed
    {
        $method = $request->method();
        $uri = $request->uri();
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        $allowedMethods = [];

        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                if ($route['method'] !== $method) {
                    $allowedMethods[] = $route['method'];
                    continue;
                }

                // Extract named route parameters
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                $request->setRouteParams($params);

                // Execute middleware pipeline
                foreach ($route['middleware'] as $mw) {
                    $middlewareInstance = is_string($mw) ? new $mw() : $mw;
                    $result = $middlewareInstance->handle($request, $response);
                    if ($result !== null && $result !== true) {
                        return $result;
                    }
                }

                // Execute controller action or closure
                return $this->executeAction($route['action'], $request, $response, $params);
            }
        }

        if (!empty($allowedMethods)) {
            $response->setStatusCode(405);
            $response->header('Allow', implode(', ', array_unique($allowedMethods)));
            if ($request->wantsJson()) {
                return $response->json(['error' => 'Method Not Allowed', 'allowed' => $allowedMethods], 405);
            }
            return view('errors/405', ['allowed' => $allowedMethods], 'layouts/main');
        }

        // 404 Not Found
        $response->setStatusCode(404);
        if ($request->wantsJson()) {
            return $response->json(['error' => 'Resource Not Found', 'path' => $uri], 404);
        }
        return view('errors/404', ['path' => $uri], 'layouts/main');
    }

    private function executeAction(mixed $action, Request $request, Response $response, array $params): mixed
    {
        if (is_callable($action)) {
            return call_user_func($action, $request, $response, ...array_values($params));
        }

        if (is_string($action)) {
            [$controllerClass, $method] = explode('@', $action);
            if (!str_starts_with($controllerClass, 'App\\')) {
                $controllerClass = "App\\Controllers\\{$controllerClass}";
            }

            if (!class_exists($controllerClass)) {
                throw new RuntimeException("Controller class [{$controllerClass}] does not exist.");
            }

            $controller = new $controllerClass();
            if (!method_exists($controller, $method)) {
                throw new RuntimeException("Method [{$method}] does not exist on controller [{$controllerClass}].");
            }

            return call_user_func([$controller, $method], $request, $response, ...array_values($params));
        }

        throw new RuntimeException("Invalid route action defined.");
    }
}
