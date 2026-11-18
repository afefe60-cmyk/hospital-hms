<?php

class CreateEmrClinicalTables
{
    public function up(): void
    {
        $db = \App\Core\Database::getInstance();

        // 1. diagnosis_catalog (ICD-10)
        $db->query("CREATE TABLE IF NOT EXISTS `diagnosis_catalog` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `icd10_code` VARCHAR(15) NOT NULL UNIQUE,
            `description_en` VARCHAR(255) NOT NULL,
            `description_ar` VARCHAR(255) NOT NULL,
            `is_infectious` TINYINT(1) NOT NULL DEFAULT 0,
            INDEX `idx_diag_code` (`icd10_code`),
            INDEX `idx_diag_search` (`description_ar`, `description_en`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. procedure_catalog
        $db->query("CREATE TABLE IF NOT EXISTS `procedure_catalog` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `code` VARCHAR(20) NOT NULL UNIQUE,
            `name_en` VARCHAR(150) NOT NULL,
            `name_ar` VARCHAR(150) NOT NULL,
            `price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. encounters table
        $db->query("CREATE TABLE IF NOT EXISTS `encounters` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `visit_id` BIGINT UNSIGNED NOT NULL,
            `doctor_id` BIGINT UNSIGNED NOT NULL,
            `encounter_type` ENUM('initial', 'follow_up', 'consultation', 'emergency') NOT NULL DEFAULT 'initial',
            `chief_complaint` TEXT NOT NULL,
            `history_present_illness` TEXT NULL,
            `physical_examination` TEXT NULL,
            `clinical_plan` TEXT NULL,
            `start_time` DATETIME NOT NULL,
            `end_time` DATETIME NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_encounters_visit` FOREIGN KEY (`visit_id`) REFERENCES `visits` (`id`),
            CONSTRAINT `fk_encounters_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`),
            INDEX `idx_encounters_visit` (`visit_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. vital_signs table
        $db->query("CREATE TABLE IF NOT EXISTS `vital_signs` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `encounter_id` BIGINT UNSIGNED NULL,
            `visit_id` BIGINT UNSIGNED NOT NULL,
            `systolic_bp` SMALLINT UNSIGNED NULL,
            `diastolic_bp` SMALLINT UNSIGNED NULL,
            `pulse_rate` SMALLINT UNSIGNED NULL,
            `respiratory_rate` SMALLINT UNSIGNED NULL,
            `temperature` DECIMAL(4, 1) NULL,
            `oxygen_saturation` SMALLINT UNSIGNED NULL,
            `random_blood_sugar` SMALLINT UNSIGNED NULL,
            `weight_kg` DECIMAL(5, 2) NULL,
            `height_cm` DECIMAL(5, 2) NULL,
            `bmi` DECIMAL(4, 1) NULL,
            `recorded_by` BIGINT UNSIGNED NOT NULL,
            `recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_vitals_encounter` FOREIGN KEY (`encounter_id`) REFERENCES `encounters` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_vitals_visit` FOREIGN KEY (`visit_id`) REFERENCES `visits` (`id`),
            CONSTRAINT `fk_vitals_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 5. diagnoses table
        $db->query("CREATE TABLE IF NOT EXISTS `diagnoses` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `encounter_id` BIGINT UNSIGNED NOT NULL,
            `diagnosis_catalog_id` INT UNSIGNED NOT NULL,
            `type` ENUM('primary', 'secondary', 'differential', 'provisional') NOT NULL DEFAULT 'primary',
            `clinical_notes` TEXT NULL,
            `diagnosed_by` BIGINT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_diagnoses_encounter` FOREIGN KEY (`encounter_id`) REFERENCES `encounters` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_diagnoses_catalog` FOREIGN KEY (`diagnosis_catalog_id`) REFERENCES `diagnosis_catalog` (`id`),
            CONSTRAINT `fk_diagnoses_doctor` FOREIGN KEY (`diagnosed_by`) REFERENCES `doctors` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 6. prescriptions table
        $db->query("CREATE TABLE IF NOT EXISTS `prescriptions` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `prescription_code` VARCHAR(30) NOT NULL UNIQUE,
            `encounter_id` BIGINT UNSIGNED NOT NULL,
            `patient_id` BIGINT UNSIGNED NOT NULL,
            `doctor_id` BIGINT UNSIGNED NOT NULL,
            `status` ENUM('pending', 'partially_dispensed', 'fully_dispensed', 'cancelled') NOT NULL DEFAULT 'pending',
            `notes` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_prescriptions_encounter` FOREIGN KEY (`encounter_id`) REFERENCES `encounters` (`id`),
            CONSTRAINT `fk_prescriptions_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
            CONSTRAINT `fk_prescriptions_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`),
            INDEX `idx_prescriptions_code` (`prescription_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 7. prescription_items table
        $db->query("CREATE TABLE IF NOT EXISTS `prescription_items` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `prescription_id` BIGINT UNSIGNED NOT NULL,
            `medicine_name` VARCHAR(150) NOT NULL,
            `dosage` VARCHAR(50) NOT NULL,
            `frequency` VARCHAR(50) NOT NULL,
            `duration_days` SMALLINT UNSIGNED NOT NULL,
            `total_quantity` SMALLINT UNSIGNED NOT NULL,
            `instructions` VARCHAR(255) NULL,
            `is_dispensed` TINYINT(1) NOT NULL DEFAULT 0,
            CONSTRAINT `fk_prescription_items_prescription` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 8. medical_orders table (Lab, Radiology, Procedures)
        $db->query("CREATE TABLE IF NOT EXISTS `medical_orders` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `encounter_id` BIGINT UNSIGNED NOT NULL,
            `visit_id` BIGINT UNSIGNED NOT NULL,
            `order_type` ENUM('lab', 'radiology', 'procedure', 'diet', 'nursing') NOT NULL,
            `order_description` VARCHAR(255) NOT NULL,
            `priority` ENUM('routine', 'urgent', 'stat') NOT NULL DEFAULT 'routine',
            `ordering_doctor_id` BIGINT UNSIGNED NOT NULL,
            `status` ENUM('ordered', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'ordered',
            `clinical_indication` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_medical_orders_encounter` FOREIGN KEY (`encounter_id`) REFERENCES `encounters` (`id`),
            CONSTRAINT `fk_medical_orders_visit` FOREIGN KEY (`visit_id`) REFERENCES `visits` (`id`),
            CONSTRAINT `fk_medical_orders_doctor` FOREIGN KEY (`ordering_doctor_id`) REFERENCES `doctors` (`id`),
            INDEX `idx_orders_type_status` (`order_type`, `status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 9. discharge_summaries table
        $db->query("CREATE TABLE IF NOT EXISTS `discharge_summaries` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `visit_id` BIGINT UNSIGNED NOT NULL,
            `discharge_date` DATETIME NOT NULL,
            `final_diagnosis` TEXT NOT NULL,
            `summary_clinical_course` TEXT NOT NULL,
            `home_medications` TEXT NOT NULL,
            `follow_up_instructions` TEXT NOT NULL,
            `discharging_doctor_id` BIGINT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_discharge_visit` FOREIGN KEY (`visit_id`) REFERENCES `visits` (`id`),
            CONSTRAINT `fk_discharge_doctor` FOREIGN KEY (`discharging_doctor_id`) REFERENCES `doctors` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(): void
    {
        $db = \App\Core\Database::getInstance();
        $db->query("DROP TABLE IF EXISTS `discharge_summaries`");
        $db->query("DROP TABLE IF EXISTS `medical_orders`");
        $db->query("DROP TABLE IF EXISTS `prescription_items`");
        $db->query("DROP TABLE IF EXISTS `prescriptions`");
        $db->query("DROP TABLE IF EXISTS `diagnoses`");
        $db->query("DROP TABLE IF EXISTS `vital_signs`");
        $db->query("DROP TABLE IF EXISTS `encounters`");
        $db->query("DROP TABLE IF EXISTS `procedure_catalog`");
        $db->query("DROP TABLE IF EXISTS `diagnosis_catalog`");
    }
}
