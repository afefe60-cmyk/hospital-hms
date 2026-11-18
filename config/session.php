<?php

return [
    'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 7200),
    'cookie_name' => 'hms_session',
    'path' => '/',
    'domain' => '',
    'secure' => filter_var($_ENV['SESSION_SECURE_COOKIE'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'httponly' => true,
    'samesite' => 'Lax', // Or Strict
    'csrf_protection' => filter_var($_ENV['CSRF_PROTECTION'] ?? true, FILTER_VALIDATE_BOOLEAN),
];
