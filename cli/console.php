<?php

/**
 * HOSPITAL HMS CLI Tool
 */

$basePath = dirname(__DIR__);

require_once $basePath . '/vendor/autoload.php';

// Initialize environment and config
\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');

$command = $argv[1] ?? 'help';

echo "==========================================\n";
echo " HOSPITAL HMS - CLI Console Tool\n";
echo "==========================================\n\n";

switch ($command) {
    case 'migrate':
        echo "Running Database Migrations...\n";
        $migrator = new \App\Core\Migrator($basePath . '/database/migrations');
        $applied = $migrator->run();
        if (empty($applied)) {
            echo "No new migrations to apply. Database is up to date.\n";
        } else {
            foreach ($applied as $m) {
                echo " [✓] Applied: {$m}\n";
            }
            echo "Migrations completed successfully.\n";
        }
        break;

    case 'status':
        echo "Checking Database Connection...\n";
        try {
            $db = \App\Core\Database::getInstance();
            $version = $db->fetchColumn("SELECT VERSION()");
            echo " [✓] Database: Connected (MySQL {$version})\n";
            echo " [✓] Timezone: " . config('app.timezone') . "\n";
            echo " [✓] Currency: " . config('app.currency') . "\n";
        } catch (\Throwable $e) {
            echo " [✗] Database Error: " . $e->getMessage() . "\n";
        }
        break;

    default:
        echo "Usage: php cli/console.php [command]\n\n";
        echo "Commands:\n";
        echo "  migrate       Run pending database migrations\n";
        echo "  status        Display system and database connection status\n";
        echo "  help          Show this help message\n";
        break;
}
