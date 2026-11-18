<?php

/**
 * HOSPITAL HMS - Integrated Hospital Management System
 * Entry Point
 */

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
