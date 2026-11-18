<?php

class CreateSecurityTables
{
    public function up(): void
    {
        $db = \App\Core\Database::getInstance();

        // 1. users table
        $db->query("CREATE TABLE IF NOT EXISTS `users` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `full_name` VARCHAR(150) NOT NULL,
            `phone` VARCHAR(25) NULL,
            `user_type` ENUM('staff', 'patient') NOT NULL DEFAULT 'staff',
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `failed_logins` TINYINT UNSIGNED NOT NULL DEFAULT 0,
            `locked_until` DATETIME NULL,
            `last_login_at` DATETIME NULL,
            `last_login_ip` VARCHAR(45) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_users_username` (`username`),
            INDEX `idx_users_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. roles table
        $db->query("CREATE TABLE IF NOT EXISTS `roles` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(50) NOT NULL UNIQUE,
            `display_name_ar` VARCHAR(100) NOT NULL,
            `display_name_en` VARCHAR(100) NOT NULL,
            `description` TEXT NULL,
            `is_system` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. permissions table
        $db->query("CREATE TABLE IF NOT EXISTS `permissions` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `code` VARCHAR(80) NOT NULL UNIQUE,
            `module` VARCHAR(50) NOT NULL,
            `action` VARCHAR(30) NOT NULL,
            `name_ar` VARCHAR(150) NOT NULL,
            `name_en` VARCHAR(150) NOT NULL,
            INDEX `idx_permissions_module` (`module`),
            INDEX `idx_permissions_code` (`code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. role_permissions pivot table
        $db->query("CREATE TABLE IF NOT EXISTS `role_permissions` (
            `role_id` INT UNSIGNED NOT NULL,
            `permission_id` INT UNSIGNED NOT NULL,
            PRIMARY KEY (`role_id`, `permission_id`),
            CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 5. user_roles pivot table
        $db->query("CREATE TABLE IF NOT EXISTS `user_roles` (
            `user_id` BIGINT UNSIGNED NOT NULL,
            `role_id` INT UNSIGNED NOT NULL,
            `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `assigned_by` BIGINT UNSIGNED NULL,
            PRIMARY KEY (`user_id`, `role_id`),
            CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_user_roles_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 6. user_departments pivot table
        $db->query("CREATE TABLE IF NOT EXISTS `user_departments` (
            `user_id` BIGINT UNSIGNED NOT NULL,
            `department_id` INT UNSIGNED NOT NULL,
            `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`user_id`, `department_id`),
            CONSTRAINT `fk_user_departments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 7. login_logs table
        $db->query("CREATE TABLE IF NOT EXISTS `login_logs` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT UNSIGNED NULL,
            `username_attempted` VARCHAR(50) NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `user_agent` VARCHAR(255) NULL,
            `status` ENUM('success', 'failed', 'locked') NOT NULL,
            `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_login_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
            INDEX `idx_login_logs_attempted` (`username_attempted`, `attempted_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 8. audit_logs table
        $db->query("CREATE TABLE IF NOT EXISTS `audit_logs` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT UNSIGNED NULL,
            `action` VARCHAR(50) NOT NULL,
            `table_name` VARCHAR(64) NOT NULL,
            `record_id` BIGINT UNSIGNED NOT NULL,
            `old_values` JSON NULL,
            `new_values` JSON NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
            INDEX `idx_audit_logs_action` (`action`),
            INDEX `idx_audit_logs_table_record` (`table_name`, `record_id`),
            INDEX `idx_audit_logs_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(): void
    {
        $db = \App\Core\Database::getInstance();
        $db->query("DROP TABLE IF EXISTS `audit_logs`");
        $db->query("DROP TABLE IF EXISTS `login_logs`");
        $db->query("DROP TABLE IF EXISTS `user_departments`");
        $db->query("DROP TABLE IF EXISTS `user_roles`");
        $db->query("DROP TABLE IF EXISTS `role_permissions`");
        $db->query("DROP TABLE IF EXISTS `permissions`");
        $db->query("DROP TABLE IF EXISTS `roles`");
        $db->query("DROP TABLE IF EXISTS `users`");
    }
}
