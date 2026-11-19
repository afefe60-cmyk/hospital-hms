<?php

$localConfig = __DIR__ . '/database.local.php';
if (file_exists($localConfig)) {
    return require $localConfig;
}

return [
    'default' => getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? 'mysql'),
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? '127.0.0.1'),
            'port' => getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? 3306),
            'database' => getenv('DB_DATABASE') ?: ($_ENV['DB_DATABASE'] ?? 'hospital_hms'),
            'username' => getenv('DB_USERNAME') ?: ($_ENV['DB_USERNAME'] ?? 'root'),
            'password' => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : ($_ENV['DB_PASSWORD'] ?? ''),
            'charset' => getenv('DB_CHARSET') ?: ($_ENV['DB_CHARSET'] ?? 'utf8mb4'),
            'collation' => getenv('DB_COLLATION') ?: ($_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci'),
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ],
        ],
    ],
];
