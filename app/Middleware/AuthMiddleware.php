<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response): mixed
    {
        if (!auth()->check()) {
            if ($request->wantsJson()) {
                return $response->json([
                    'error' => 'Unauthenticated',
                    'message' => 'يجب تسجيل الدخول أولاً للوصول إلى هذا الرابط.'
                ], 401);
            }

            Session::flash('error', 'يجب تسجيل الدخول أولاً للوصول إلى هذا الرابط.');
            $response->redirect(url('/login'));
            return false;
        }

        return true;
    }
}
