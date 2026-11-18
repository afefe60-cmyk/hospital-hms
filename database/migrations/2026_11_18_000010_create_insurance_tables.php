<?php

use App\Core\Database;

class CreateInsuranceTables
{
    public function up(): void
    {
        $db = Database::getInstance();

        // 1. insurance_companies
        $db->query("CREATE TABLE IF NOT EXISTS `insurance_companies` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `code` VARCHAR(30) UNIQUE NOT NULL,
            `name_ar` VARCHAR(150) NOT NULL,
            `name_en` VARCHAR(150) NOT NULL,
            `account_id` INT UNSIGNED NULL,
            `phone` VARCHAR(25) NOT NULL,
            `email` VARCHAR(100) NULL,
            `address` VARCHAR(255) NULL,
            `default_coverage_rate` DECIMAL(5,2) NOT NULL DEFAULT 80.00,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_ins_companies_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
            INDEX `idx_ins_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. patient_insurance
        $db->query("CREATE TABLE IF NOT EXISTS `patient_insurance` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `patient_id` BIGINT UNSIGNED NOT NULL,
            `company_id` INT UNSIGNED NOT NULL,
            `policy_number` VARCHAR(50) NOT NULL,
            `member_number` VARCHAR(50) NULL,
            `coverage_percentage` DECIMAL(5,2) NOT NULL DEFAULT 80.00,
            `copay_limit` DECIMAL(12,2) NULL,
            `start_date` DATE NULL,
            `expiry_date` DATE NOT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_pat_ins_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_pat_ins_company` FOREIGN KEY (`company_id`) REFERENCES `insurance_companies` (`id`) ON DELETE RESTRICT,
            INDEX `idx_pat_ins_lookup` (`patient_id`, `is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. insurance_claims
        $db->query("CREATE TABLE IF NOT EXISTS `insurance_claims` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `claim_number` VARCHAR(30) UNIQUE NOT NULL,
            `company_id` INT UNSIGNED NOT NULL,
            `patient_id` BIGINT UNSIGNED NOT NULL,
            `invoice_id` BIGINT UNSIGNED NOT NULL,
            `policy_number` VARCHAR(50) NOT NULL,
            `claim_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `approved_amount` DECIMAL(15,2) NULL,
            `rejected_amount` DECIMAL(15,2) NULL,
            `status` ENUM('submitted', 'under_review', 'approved', 'partially_approved', 'rejected', 'settled') NOT NULL DEFAULT 'submitted',
            `submission_date` DATE NOT NULL,
            `settlement_date` DATE NULL,
            `rejection_reason` TEXT NULL,
            `notes` TEXT NULL,
            `created_by` BIGINT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_claims_company` FOREIGN KEY (`company_id`) REFERENCES `insurance_companies` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_claims_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_claims_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_claims_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
            INDEX `idx_claims_status` (`status`),
            INDEX `idx_claims_comp_date` (`company_id`, `submission_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(): void
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS `insurance_claims`");
        $db->query("DROP TABLE IF EXISTS `patient_insurance`");
        $db->query("DROP TABLE IF EXISTS `insurance_companies`");
    }
}
