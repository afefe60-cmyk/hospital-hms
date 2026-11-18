<?php

namespace App\Core;

class Migrator
{
    private Database $db;
    private string $migrationsPath;

    public function __construct(string $migrationsPath)
    {
        $this->db = Database::getInstance();
        $this->migrationsPath = rtrim($migrationsPath, '/\\');
        $this->ensureMigrationTable();
    }

    private function ensureMigrationTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL UNIQUE,
            `batch` INT UNSIGNED NOT NULL,
            `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $this->db->query($sql);
    }

    public function run(): array
    {
        $appliedMigrations = $this->db->fetchAll("SELECT migration FROM `migrations`");
        $appliedList = array_column($appliedMigrations, 'migration');

        $files = glob($this->migrationsPath . '/*.php');
        sort($files);

        $currentBatch = (int)$this->db->fetchColumn("SELECT MAX(batch) FROM `migrations`") + 1;
        $newlyApplied = [];

        foreach ($files as $file) {
            $migrationName = basename($file, '.php');

            if (!in_array($migrationName, $appliedList)) {
                require_once $file;

                // Extract class name from file
                $className = $this->resolveClassName($file);
                if (class_exists($className)) {
                    $migrationInstance = new $className();
                    if (method_exists($migrationInstance, 'up')) {
                        $migrationInstance->up();
                        $this->db->query(
                            "INSERT INTO `migrations` (`migration`, `batch`) VALUES (:migration, :batch)",
                            ['migration' => $migrationName, 'batch' => $currentBatch]
                        );
                        $newlyApplied[] = $migrationName;
                    }
                }
            }
        }

        return $newlyApplied;
    }

    private function resolveClassName(string $file): string
    {
        $content = file_get_contents($file);
        if (preg_match('/class\s+([a-zA-Z0-9_]+)/', $content, $matches)) {
            return $matches[1];
        }
        return '';
    }
}
