<?php

/** @var \App\Core\Router $router */

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;

// Public & Home Routes
$router->get('/', 'HomeController@index');
$router->get('/lang', 'HomeController@switchLanguage');

// Healthcheck Route
$router->get('/health', function (\App\Core\Request $request, \App\Core\Response $response) {
    try {
        $db = \App\Core\Database::getInstance();
        $dbStatus = true;
    } catch (\Throwable $e) {
        $dbStatus = false;
    }

    return $response->json([
        'status' => 'healthy',
        'database' => $dbStatus,
        'php' => PHP_VERSION,
        'timestamp' => date('c'),
    ]);
});

// Authentication Routes (Guests only)
$router->get('/login', 'AuthController@showLogin', [GuestMiddleware::class]);
$router->post('/login', 'AuthController@login', [GuestMiddleware::class, CsrfMiddleware::class]);

// Authenticated Routes
$router->post('/logout', 'AuthController@logout', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/profile', 'AuthController@profile', [AuthMiddleware::class]);
$router->post('/profile/password', 'AuthController@updatePassword', [AuthMiddleware::class, CsrfMiddleware::class]);

// Users Management Routes
$router->get('/users', 'UserController@index', [AuthMiddleware::class]);
$router->get('/users/create', 'UserController@create', [AuthMiddleware::class]);
$router->post('/users', 'UserController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/users/{id}/edit', 'UserController@edit', [AuthMiddleware::class]);
$router->post('/users/{id}', 'UserController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/users/{id}/toggle-status', 'UserController@toggleStatus', [AuthMiddleware::class, CsrfMiddleware::class]);

// Roles & Permissions RBAC Routes
$router->get('/roles', 'RoleController@index', [AuthMiddleware::class]);
$router->get('/roles/{id}/permissions', 'RoleController@permissions', [AuthMiddleware::class]);
$router->post('/roles/{id}/permissions', 'RoleController@updatePermissions', [AuthMiddleware::class, CsrfMiddleware::class]);

// Audit Logs
$router->get('/audit-logs', 'AuditLogController@index', [AuthMiddleware::class]);
