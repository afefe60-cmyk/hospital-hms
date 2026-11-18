<?php

/**
 * HOSPITAL HMS - Integrated Hospital Management System
 * Entry Point
 */

// If requested via PHP CLI server and the file exists, serve it directly
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $path;
    if (is_file($file)) {
        return false;
    }
}

define('HMS_START', microtime(true));

// Base Path
$basePath = dirname(__DIR__);

// 1. Check Composer Autoloader or fallback to custom PSR-4 autoloader
if (file_exists($basePath . '/vendor/autoload.php')) {
    require_once $basePath . '/vendor/autoload.php';
} else {
    // Custom native PSR-4 autoloader fallback
    spl_autoload_register(function ($class) use ($basePath) {
        $prefix = 'App\\';
        $baseDir = $basePath . '/app/';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    });

    require_once $basePath . '/app/Helpers/functions.php';
}

// 2. Initialize and Run Application
$app = new \App\Core\App($basePath);
$app->run();
