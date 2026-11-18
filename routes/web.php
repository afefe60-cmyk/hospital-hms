<?php

/** @var \App\Core\Router $router */

$router->get('/', 'HomeController@index');
$router->get('/lang', 'HomeController@switchLanguage');

// Healthcheck route
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
