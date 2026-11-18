<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'HOSPITAL HMS',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Africa/Khartoum',
    'locale' => $_ENV['APP_LOCALE'] ?? 'ar',
    'fallback_locale' => $_ENV['APP_FALLBACK_LOCALE'] ?? 'en',
    'currency' => 'SDG',
    'currency_symbol' => 'ج.س',
];
