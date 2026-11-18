<?php

use App\Core\Database;

class CreateSurgeryAndIcuTables
{
    public function up(): void
    {
        $db = Database::getInstance();

        // 1. surgeries
        $db->query("CREATE TABLE IF NOT EXISTS `surgeries` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `surgery_number` VARCHAR(30) UNIQUE NOT NULL,
            `patient_id` BIGINT UNSIGNED NOT NULL,
            `admission_id` BIGINT UNSIGNED NULL,
            `operating_room_id` INT UNSIGNED NOT NULL,
            `lead_surgeon_id` BIGINT UNSIGNED NOT NULL,
            `assistant_surgeon_id` BIGINT UNSIGNED NULL,
            `anesthesiologist_id` BIGINT UNSIGNED NULL,
            `scrub_nurse_id` BIGINT UNSIGNED NULL,
            `surgery_type` ENUM('elective', 'emergency') NOT NULL DEFAULT 'elective',
            `procedure_name` VARCHAR(255) NOT NULL,
            `scheduled_start` DATETIME NOT NULL,
            `scheduled_end` DATETIME NOT NULL,
            `actual_start` DATETIME NULL,
            `actual_end` DATETIME NULL,
            `status` ENUM('scheduled', 'in_progress', 'completed', 'postponed', 'cancelled') NOT NULL DEFAULT 'scheduled',
            `pre_op_diagnosis` TEXT NULL,
            `post_op_diagnosis` TEXT NULL,
            `surgery_notes` LONGTEXT NULL,
            `created_by` BIGINT UNSIGNED NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_surgeries_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_surgeries_admission` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_surgeries_or` FOREIGN KEY (`operating_room_id`) REFERENCES `operating_rooms` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_surgeries_lead_surgeon` FOREIGN KEY (`lead_surgeon_id`) REFERENCES `doctors` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_surgeries_assistant_surgeon` FOREIGN KEY (`assistant_surgeon_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_surgeries_anesthesiologist` FOREIGN KEY (`anesthesiologist_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_surgeries_nurse` FOREIGN KEY (`scrub_nurse_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_surgeries_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
            INDEX `idx_surgeries_status` (`status`),
            INDEX `idx_surgeries_dates` (`scheduled_start`, `scheduled_end`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. anesthesia_records
        $db->query("CREATE TABLE IF NOT EXISTS `anesthesia_records` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `surgery_id` BIGINT UNSIGNED NOT NULL UNIQUE,
            `anesthesiologist_id` BIGINT UNSIGNED NOT NULL,
            `anesthesia_type` ENUM('general', 'spinal', 'epidural', 'local', 'sedation', 'regional') NOT NULL DEFAULT 'general',
            `asa_physical_status` ENUM('I', 'II', 'III', 'IV', 'V', 'VI', 'E') NOT NULL DEFAULT 'I',
            `pre_anesthesia_assessment` TEXT NULL,
            `airway_assessment` VARCHAR(255) NULL,
            `start_time` DATETIME NULL,
            `end_time` DATETIME NULL,
            `agents_used` TEXT NULL,
            `recovery_score` VARCHAR(50) NULL,
            `complications` TEXT NULL,
            `anesthesia_notes` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_anesthesia_surgery` FOREIGN KEY (`surgery_id`) REFERENCES `surgeries` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_anesthesia_doctor` FOREIGN KEY (`anesthesiologist_id`) REFERENCES `doctors` (`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. icu_stays
        $db->query("CREATE TABLE IF NOT EXISTS `icu_stays` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `stay_number` VARCHAR(30) UNIQUE NOT NULL,
            `patient_id` BIGINT UNSIGNED NOT NULL,
            `admission_id` BIGINT UNSIGNED NULL,
            `bed_id` INT UNSIGNED NOT NULL,
            `attending_intensivist_id` BIGINT UNSIGNED NULL,
            `admitted_at` DATETIME NOT NULL,
            `discharged_at` DATETIME NULL,
            `admission_reason` TEXT NOT NULL,
            `apache_score` INT UNSIGNED NULL,
            `ventilator_status` ENUM('none', 'invasive', 'non_invasive') NOT NULL DEFAULT 'none',
            `status` ENUM('admitted', 'transferred', 'discharged', 'deceased') NOT NULL DEFAULT 'admitted',
            `discharge_summary` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_icu_stays_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_icu_stays_admission` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_icu_stays_bed` FOREIGN KEY (`bed_id`) REFERENCES `beds` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_icu_stays_doctor` FOREIGN KEY (`attending_intensivist_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL,
            INDEX `idx_icu_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. icu_observations
        $db->query("CREATE TABLE IF NOT EXISTS `icu_observations` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `icu_stay_id` BIGINT UNSIGNED NOT NULL,
            `recorded_at` DATETIME NOT NULL,
            `recorded_by` BIGINT UNSIGNED NOT NULL,
            `systolic_bp` INT UNSIGNED NULL,
            `diastolic_bp` INT UNSIGNED NULL,
            `pulse_rate` INT UNSIGNED NULL,
            `respiratory_rate` INT UNSIGNED NULL,
            `temperature` DECIMAL(4,1) NULL,
            `oxygen_saturation` DECIMAL(4,1) NULL,
            `gcs_score` TINYINT UNSIGNED NULL,
            `pupil_reaction` ENUM('brisk', 'sluggish', 'fixed', 'unequal') NULL DEFAULT 'brisk',
            `ventilator_mode` VARCHAR(50) NULL,
            `fio2` INT UNSIGNED NULL,
            `peep` INT UNSIGNED NULL,
            `urine_output_ml` INT UNSIGNED NULL,
            `iv_fluids_ml` INT UNSIGNED NULL,
            `arterial_blood_gas` TEXT NULL,
            `clinical_notes` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_icu_obs_stay` FOREIGN KEY (`icu_stay_id`) REFERENCES `icu_stays` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_icu_obs_recorder` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
            INDEX `idx_icu_obs_time` (`icu_stay_id`, `recorded_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(): void
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS `icu_observations`");
        $db->query("DROP TABLE IF EXISTS `icu_stays`");
        $db->query("DROP TABLE IF EXISTS `anesthesia_records`");
        $db->query("DROP TABLE IF EXISTS `surgeries`");
    }
}
