<?php

class CreateLaboratoryAndRadiologyTables
{
    public function up(): void
    {
        $db = \App\Core\Database::getInstance();

        // 1. lab_tests (Catalog of tests available in hospital)
        $db->query("CREATE TABLE IF NOT EXISTS `lab_tests` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `code` VARCHAR(30) NOT NULL UNIQUE,
            `name_en` VARCHAR(150) NOT NULL,
            `name_ar` VARCHAR(150) NOT NULL,
            `category` VARCHAR(50) NOT NULL DEFAULT 'hematology', -- hematology, biochemistry, microbiology, serology, parasitology, urine
            `sample_type` VARCHAR(50) NOT NULL DEFAULT 'whole_blood', -- whole_blood, serum, plasma, urine, stool, csf, swab
            `price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            `normal_range` VARCHAR(100) NULL,
            `unit` VARCHAR(30) NULL,
            `turnaround_time_hours` SMALLINT UNSIGNED NOT NULL DEFAULT 2,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_lab_category` (`category`),
            INDEX `idx_lab_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. lab_orders
        $db->query("CREATE TABLE IF NOT EXISTS `lab_orders` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `order_number` VARCHAR(30) NOT NULL UNIQUE,
            `patient_id` BIGINT UNSIGNED NOT NULL,
            `doctor_id` BIGINT UNSIGNED NOT NULL,
            `encounter_id` BIGINT UNSIGNED NULL,
            `admission_id` BIGINT UNSIGNED NULL,
            `priority` ENUM('routine', 'urgent', 'stat') NOT NULL DEFAULT 'routine',
            `clinical_indication` TEXT NULL,
            `status` ENUM('ordered', 'sample_collected', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'ordered',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_lab_ord_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
            CONSTRAINT `fk_lab_ord_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`),
            CONSTRAINT `fk_lab_ord_encounter` FOREIGN KEY (`encounter_id`) REFERENCES `encounters` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_lab_ord_admission` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL,
            INDEX `idx_lab_ord_status` (`status`),
            INDEX `idx_lab_ord_priority` (`priority`),
            INDEX `idx_lab_ord_patient` (`patient_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. lab_order_items (Mapping of tests in a single order)
        $db->query("CREATE TABLE IF NOT EXISTS `lab_order_items` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `lab_order_id` BIGINT UNSIGNED NOT NULL,
            `lab_test_id` INT UNSIGNED NOT NULL,
            `price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            CONSTRAINT `fk_loi_order` FOREIGN KEY (`lab_order_id`) REFERENCES `lab_orders` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_loi_test` FOREIGN KEY (`lab_test_id`) REFERENCES `lab_tests` (`id`),
            INDEX `idx_loi_order` (`lab_order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. lab_samples (Sample tracking with barcode)
        $db->query("CREATE TABLE IF NOT EXISTS `lab_samples` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `lab_order_id` BIGINT UNSIGNED NOT NULL,
            `sample_barcode` VARCHAR(50) NOT NULL UNIQUE,
            `sample_type` VARCHAR(50) NOT NULL,
            `collected_at` DATETIME NOT NULL,
            `collected_by` BIGINT UNSIGNED NOT NULL,
            `status` ENUM('collected', 'received', 'rejected') NOT NULL DEFAULT 'collected',
            `rejection_reason` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_ls_order` FOREIGN KEY (`lab_order_id`) REFERENCES `lab_orders` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_ls_collector` FOREIGN KEY (`collected_by`) REFERENCES `users` (`id`),
            INDEX `idx_ls_barcode` (`sample_barcode`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 5. lab_results (Main verification record)
        $db->query("CREATE TABLE IF NOT EXISTS `lab_results` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `lab_order_id` BIGINT UNSIGNED NOT NULL UNIQUE,
            `verified_by` BIGINT UNSIGNED NULL,
            `verified_at` DATETIME NULL,
            `status` ENUM('draft', 'approved', 'critical') NOT NULL DEFAULT 'draft',
            `general_comments` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_lr_order` FOREIGN KEY (`lab_order_id`) REFERENCES `lab_orders` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_lr_verifier` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
            INDEX `idx_lr_order` (`lab_order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 6. lab_result_items (Granular result parameters with reference ranges)
        $db->query("CREATE TABLE IF NOT EXISTS `lab_result_items` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `lab_result_id` BIGINT UNSIGNED NOT NULL,
            `lab_test_id` INT UNSIGNED NOT NULL,
            `parameter_name` VARCHAR(100) NOT NULL,
            `result_value` VARCHAR(100) NOT NULL,
            `unit` VARCHAR(30) NULL,
            `reference_range` VARCHAR(100) NULL,
            `flag` ENUM('normal', 'high', 'low', 'critical', 'positive', 'negative') NOT NULL DEFAULT 'normal',
            CONSTRAINT `fk_lri_result` FOREIGN KEY (`lab_result_id`) REFERENCES `lab_results` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_lri_test` FOREIGN KEY (`lab_test_id`) REFERENCES `lab_tests` (`id`),
            INDEX `idx_lri_result` (`lab_result_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 7. radiology_services (Catalog of imaging services)
        $db->query("CREATE TABLE IF NOT EXISTS `radiology_services` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `code` VARCHAR(30) NOT NULL UNIQUE,
            `name_en` VARCHAR(150) NOT NULL,
            `name_ar` VARCHAR(150) NOT NULL,
            `modality` ENUM('x_ray', 'ultrasound', 'ct_scan', 'mri', 'echo', 'fluoroscopy') NOT NULL DEFAULT 'x_ray',
            `price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            `preparation_instructions` TEXT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_rad_modality` (`modality`),
            INDEX `idx_rad_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 8. radiology_orders
        $db->query("CREATE TABLE IF NOT EXISTS `radiology_orders` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `order_number` VARCHAR(30) NOT NULL UNIQUE,
            `patient_id` BIGINT UNSIGNED NOT NULL,
            `doctor_id` BIGINT UNSIGNED NOT NULL,
            `encounter_id` BIGINT UNSIGNED NULL,
            `admission_id` BIGINT UNSIGNED NULL,
            `service_id` INT UNSIGNED NOT NULL,
            `priority` ENUM('routine', 'urgent', 'stat') NOT NULL DEFAULT 'routine',
            `clinical_indication` TEXT NOT NULL,
            `status` ENUM('ordered', 'scheduled', 'performed', 'reported', 'cancelled') NOT NULL DEFAULT 'ordered',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_rad_ord_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
            CONSTRAINT `fk_rad_ord_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`),
            CONSTRAINT `fk_rad_ord_encounter` FOREIGN KEY (`encounter_id`) REFERENCES `encounters` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_rad_ord_admission` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_rad_ord_service` FOREIGN KEY (`service_id`) REFERENCES `radiology_services` (`id`),
            INDEX `idx_rad_ord_status` (`status`),
            INDEX `idx_rad_ord_patient` (`patient_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 9. radiology_reports
        $db->query("CREATE TABLE IF NOT EXISTS `radiology_reports` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `radiology_order_id` BIGINT UNSIGNED NOT NULL UNIQUE,
            `radiologist_id` BIGINT UNSIGNED NOT NULL,
            `findings` TEXT NOT NULL,
            `impression` TEXT NOT NULL,
            `reported_at` DATETIME NOT NULL,
            `status` ENUM('preliminary', 'final') NOT NULL DEFAULT 'final',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_rad_rep_order` FOREIGN KEY (`radiology_order_id`) REFERENCES `radiology_orders` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_rad_rep_radiologist` FOREIGN KEY (`radiologist_id`) REFERENCES `doctors` (`id`),
            INDEX `idx_rad_rep_order` (`radiology_order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 10. radiology_attachments
        $db->query("CREATE TABLE IF NOT EXISTS `radiology_attachments` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `radiology_order_id` BIGINT UNSIGNED NOT NULL,
            `file_name` VARCHAR(255) NOT NULL,
            `file_path` VARCHAR(255) NOT NULL,
            `file_type` VARCHAR(50) NOT NULL DEFAULT 'image',
            `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_rad_att_order` FOREIGN KEY (`radiology_order_id`) REFERENCES `radiology_orders` (`id`) ON DELETE CASCADE,
            INDEX `idx_rad_att_order` (`radiology_order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(): void
    {
        $db = \App\Core\Database::getInstance();
        $db->query("DROP TABLE IF EXISTS `radiology_attachments`;");
        $db->query("DROP TABLE IF EXISTS `radiology_reports`;");
        $db->query("DROP TABLE IF EXISTS `radiology_orders`;");
        $db->query("DROP TABLE IF EXISTS `radiology_services`;");
        $db->query("DROP TABLE IF EXISTS `lab_result_items`;");
        $db->query("DROP TABLE IF EXISTS `lab_results`;");
        $db->query("DROP TABLE IF EXISTS `lab_samples`;");
        $db->query("DROP TABLE IF EXISTS `lab_order_items`;");
        $db->query("DROP TABLE IF EXISTS `lab_orders`;");
        $db->query("DROP TABLE IF EXISTS `lab_tests`;");
    }
}
