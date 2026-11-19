<?php

use App\Core\Database;

class CreateSystemSettingsAndAttachmentsTables
{
    public function up(): void
    {
        $db = Database::getInstance();

        // 1. system_settings (Hospital system configuration, identity, parameters)
        $db->query("CREATE TABLE IF NOT EXISTS `system_settings` (
            `setting_key` VARCHAR(50) PRIMARY KEY,
            `setting_value` TEXT NULL,
            `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
            `description` VARCHAR(255) NULL,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_settings_group` (`setting_group`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. attachments (Universal secure uploaded files & attachments)
        $db->query("CREATE TABLE IF NOT EXISTS `attachments` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `attachable_type` VARCHAR(100) NOT NULL,
            `attachable_id` BIGINT UNSIGNED NOT NULL,
            `file_name` VARCHAR(255) NOT NULL,
            `file_path` VARCHAR(255) NOT NULL,
            `file_mime` VARCHAR(100) NOT NULL,
            `file_size_bytes` INT UNSIGNED NOT NULL,
            `uploaded_by` BIGINT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_attachments_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            INDEX `idx_attachable_lookup` (`attachable_type`, `attachable_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(): void
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS `attachments`");
        $db->query("DROP TABLE IF EXISTS `system_settings`");
    }
}
