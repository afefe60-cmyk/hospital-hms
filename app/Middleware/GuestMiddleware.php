<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response): mixed
    {
        if (auth()->check()) {
            $response->redirect(url('/'));
            return false;
        }

        return true;
    }
}
