<?php

/** @var \App\Core\Router $router */

$router->group(['prefix' => '/api/v1'], function (\App\Core\Router $router) {
    $router->get('/status', function (\App\Core\Request $request, \App\Core\Response $response) {
        return $response->json([
            'system' => config('app.name'),
            'version' => '1.0.0',
            'api_version' => 'v1',
            'currency' => config('app.currency'),
            'timezone' => config('app.timezone'),
        ]);
    });
});
