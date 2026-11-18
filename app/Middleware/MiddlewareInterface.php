<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

interface MiddlewareInterface
{
    /**
     * Handle an incoming request
     * Return null or true to continue pipeline, or a Response object to stop execution
     */
    public function handle(Request $request, Response $response): mixed;
}
