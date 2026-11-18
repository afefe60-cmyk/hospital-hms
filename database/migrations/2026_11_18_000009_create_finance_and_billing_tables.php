<?php

use App\Core\Database;

class CreateFinanceAndBillingTables
{
    public function up(): void
    {
        $db = Database::getInstance();

        // 1. cost_centers
        $db->query("CREATE TABLE IF NOT EXISTS `cost_centers` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `code` VARCHAR(30) UNIQUE NOT NULL,
            `name_ar` VARCHAR(100) NOT NULL,
            `name_en` VARCHAR(100) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. accounts (General Ledger Chart of Accounts)
        $db->query("CREATE TABLE IF NOT EXISTS `accounts` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `account_code` VARCHAR(25) UNIQUE NOT NULL,
            `account_name_ar` VARCHAR(150) NOT NULL,
            `account_name_en` VARCHAR(150) NOT NULL,
            `parent_id` INT UNSIGNED NULL,
            `account_type` ENUM('asset', 'liability', 'equity', 'revenue', 'expense') NOT NULL,
            `is_transactional` TINYINT(1) NOT NULL DEFAULT 1,
            `current_balance` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_accounts_parent` FOREIGN KEY (`parent_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
            INDEX `idx_accounts_type` (`account_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. service_catalog (Medical Services Price List)
        $db->query("CREATE TABLE IF NOT EXISTS `service_catalog` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `code` VARCHAR(30) UNIQUE NOT NULL,
            `name_ar` VARCHAR(150) NOT NULL,
            `name_en` VARCHAR(150) NOT NULL,
            `department_id` INT UNSIGNED NOT NULL,
            `revenue_account_id` INT UNSIGNED NULL,
            `price_standard` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_services_dep` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_services_account` FOREIGN KEY (`revenue_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
            INDEX `idx_services_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. cashboxes (Points of sale / cash drawers)
        $db->query("CREATE TABLE IF NOT EXISTS `cashboxes` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `code` VARCHAR(30) UNIQUE NOT NULL,
            `name_ar` VARCHAR(100) NOT NULL,
            `name_en` VARCHAR(100) NOT NULL,
            `account_id` INT UNSIGNED NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_cashboxes_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 5. cashbox_sessions (Cashier shifts)
        $db->query("CREATE TABLE IF NOT EXISTS `cashbox_sessions` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `session_number` VARCHAR(30) UNIQUE NOT NULL,
            `cashbox_id` INT UNSIGNED NOT NULL,
            `cashier_user_id` BIGINT UNSIGNED NOT NULL,
            `opened_at` DATETIME NOT NULL,
            `opening_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `closed_at` DATETIME NULL,
            `expected_closing` DECIMAL(15,2) NULL,
            `actual_closing` DECIMAL(15,2) NULL,
            `difference` DECIMAL(15,2) NULL,
            `notes` TEXT NULL,
            `status` ENUM('open', 'closed', 'reconciled') NOT NULL DEFAULT 'open',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_sessions_box` FOREIGN KEY (`cashbox_id`) REFERENCES `cashboxes` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_sessions_cashier` FOREIGN KEY (`cashier_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
            INDEX `idx_sessions_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 6. invoices
        $db->query("CREATE TABLE IF NOT EXISTS `invoices` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `invoice_number` VARCHAR(30) UNIQUE NOT NULL,
            `visit_id` BIGINT UNSIGNED NULL,
            `patient_id` BIGINT UNSIGNED NOT NULL,
            `admission_id` BIGINT UNSIGNED NULL,
            `total_gross` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `total_discount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `total_insurance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `total_patient_due` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `paid_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `status` ENUM('draft', 'issued', 'partially_paid', 'paid', 'cancelled') NOT NULL DEFAULT 'issued',
            `created_by` BIGINT UNSIGNED NOT NULL,
            `notes` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_invoices_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_invoices_visit` FOREIGN KEY (`visit_id`) REFERENCES `visits` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_invoices_admission` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_invoices_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
            INDEX `idx_invoices_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 7. invoice_items
        $db->query("CREATE TABLE IF NOT EXISTS `invoice_items` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `invoice_id` BIGINT UNSIGNED NOT NULL,
            `service_id` INT UNSIGNED NULL,
            `item_type` ENUM('service', 'medicine', 'lab_test', 'radiology', 'bed_stay', 'other') NOT NULL DEFAULT 'service',
            `description` VARCHAR(255) NOT NULL,
            `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
            `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_inv_items_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_inv_items_service` FOREIGN KEY (`service_id`) REFERENCES `service_catalog` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 8. payments (Receipts / التحصيل)
        $db->query("CREATE TABLE IF NOT EXISTS `payments` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `receipt_number` VARCHAR(30) UNIQUE NOT NULL,
            `invoice_id` BIGINT UNSIGNED NOT NULL,
            `session_id` BIGINT UNSIGNED NULL,
            `amount` DECIMAL(15,2) NOT NULL,
            `payment_method` ENUM('cash', 'bankak', 'card', 'bank_transfer') NOT NULL DEFAULT 'cash',
            `transaction_reference` VARCHAR(100) NULL,
            `notes` TEXT NULL,
            `collected_by` BIGINT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_payments_session` FOREIGN KEY (`session_id`) REFERENCES `cashbox_sessions` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_payments_collector` FOREIGN KEY (`collected_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 9. journal_entries
        $db->query("CREATE TABLE IF NOT EXISTS `journal_entries` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `entry_number` VARCHAR(30) UNIQUE NOT NULL,
            `entry_date` DATE NOT NULL,
            `description` VARCHAR(255) NOT NULL,
            `source_document` VARCHAR(50) NULL,
            `is_posted` TINYINT(1) NOT NULL DEFAULT 1,
            `posted_by` BIGINT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_journal_posted_by` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 10. journal_details
        $db->query("CREATE TABLE IF NOT EXISTS `journal_details` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `journal_entry_id` BIGINT UNSIGNED NOT NULL,
            `account_id` INT UNSIGNED NOT NULL,
            `cost_center_id` INT UNSIGNED NULL,
            `debit` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
            `credit` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
            CONSTRAINT `fk_jdetails_entry` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_jdetails_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_jdetails_cc` FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(): void
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS `journal_details`");
        $db->query("DROP TABLE IF EXISTS `journal_entries`");
        $db->query("DROP TABLE IF EXISTS `payments`");
        $db->query("DROP TABLE IF EXISTS `invoice_items`");
        $db->query("DROP TABLE IF EXISTS `invoices`");
        $db->query("DROP TABLE IF EXISTS `cashbox_sessions`");
        $db->query("DROP TABLE IF EXISTS `cashboxes`");
        $db->query("DROP TABLE IF EXISTS `service_catalog`");
        $db->query("DROP TABLE IF EXISTS `accounts`");
        $db->query("DROP TABLE IF EXISTS `cost_centers`");
    }
}
