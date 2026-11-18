<?php

namespace App\Middleware;

use App\Core\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response): mixed
    {
        // Only verify state-changing HTTP methods
        if (!in_array($request->method(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return true;
        }

        // If CSRF protection is disabled in config
        if (!Config::get('session.csrf_protection', true)) {
            return true;
        }

        $token = $request->input('_csrf_token') ?? $request->header('x-csrf-token');

        if (!Session::validateToken($token)) {
            $response->setStatusCode(419);
            if ($request->wantsJson()) {
                return $response->json([
                    'error' => 'CSRF Token Mismatch',
                    'message' => 'انتهت صلاحية جلسة العمل أو رمز الأمان غير مطابق. يرجى إعادة تحديث الصفحة.'
                ], 419);
            }

            return view('errors/419', [], 'layouts/main');
        }

        return true;
    }
}
