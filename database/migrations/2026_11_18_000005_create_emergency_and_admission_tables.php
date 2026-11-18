<?php

class CreateEmergencyAndAdmissionTables
{
    public function up(): void
    {
        $db = \App\Core\Database::getInstance();

        // 1. emergency_cases table
        $db->query("CREATE TABLE IF NOT EXISTS `emergency_cases` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `case_number` VARCHAR(30) NOT NULL UNIQUE,
            `patient_id` BIGINT UNSIGNED NULL,
            `temporary_patient_name` VARCHAR(150) NULL,
            `arrival_mode` ENUM('ambulance', 'walk_in', 'police', 'transfer') NOT NULL DEFAULT 'walk_in',
            `arrival_time` DATETIME NOT NULL,
            `triage_level` ENUM('red', 'orange', 'yellow', 'green', 'blue') NOT NULL DEFAULT 'yellow',
            `chief_complaint` TEXT NOT NULL,
            `attending_doctor_id` BIGINT UNSIGNED NULL,
            `status` ENUM('triaged', 'under_treatment', 'admitted', 'discharged', 'transferred', 'deceased') NOT NULL DEFAULT 'triaged',
            `outcome_notes` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_er_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_er_doctor` FOREIGN KEY (`attending_doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL,
            INDEX `idx_er_status` (`status`),
            INDEX `idx_er_triage` (`triage_level`),
            INDEX `idx_er_arrival` (`arrival_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. triage_assessments table
        $db->query("CREATE TABLE IF NOT EXISTS `triage_assessments` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `emergency_case_id` BIGINT UNSIGNED NOT NULL,
            `triage_color` ENUM('red', 'orange', 'yellow', 'green', 'blue') NOT NULL,
            `gcs_score` TINYINT UNSIGNED NULL,
            `airway_status` ENUM('clear', 'threatened', 'obstructed') NOT NULL DEFAULT 'clear',
            `breathing_status` ENUM('normal', 'dyspnea', 'apnea') NOT NULL DEFAULT 'normal',
            `circulation_status` ENUM('stable', 'weak', 'pulseless') NOT NULL DEFAULT 'stable',
            `systolic_bp` INT NULL,
            `diastolic_bp` INT NULL,
            `pulse_rate` INT NULL,
            `respiratory_rate` INT NULL,
            `temperature` DECIMAL(4, 1) NULL,
            `oxygen_saturation` INT NULL,
            `pain_score` TINYINT UNSIGNED NULL,
            `triage_nurse_id` BIGINT UNSIGNED NOT NULL,
            `notes` TEXT NULL,
            `assessed_at` DATETIME NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_triage_case` FOREIGN KEY (`emergency_case_id`) REFERENCES `emergency_cases` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_triage_nurse` FOREIGN KEY (`triage_nurse_id`) REFERENCES `users` (`id`),
            INDEX `idx_triage_case` (`emergency_case_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. admissions table (IPD)
        $db->query("CREATE TABLE IF NOT EXISTS `admissions` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `admission_number` VARCHAR(30) NOT NULL UNIQUE,
            `patient_id` BIGINT UNSIGNED NOT NULL,
            `admitting_doctor_id` BIGINT UNSIGNED NOT NULL,
            `department_id` INT UNSIGNED NOT NULL,
            `emergency_case_id` BIGINT UNSIGNED NULL,
            `admission_date` DATETIME NOT NULL,
            `discharge_date` DATETIME NULL,
            `admission_type` ENUM('emergency', 'elective', 'transfer') NOT NULL DEFAULT 'elective',
            `admission_reason` TEXT NOT NULL,
            `provisional_diagnosis` TEXT NOT NULL,
            `status` ENUM('admitted', 'discharged', 'transferred', 'against_medical_advice', 'deceased') NOT NULL DEFAULT 'admitted',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_adm_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
            CONSTRAINT `fk_adm_doctor` FOREIGN KEY (`admitting_doctor_id`) REFERENCES `doctors` (`id`),
            CONSTRAINT `fk_adm_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
            CONSTRAINT `fk_adm_er_case` FOREIGN KEY (`emergency_case_id`) REFERENCES `emergency_cases` (`id`) ON DELETE SET NULL,
            INDEX `idx_adm_patient` (`patient_id`),
            INDEX `idx_adm_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. bed_assignments table
        $db->query("CREATE TABLE IF NOT EXISTS `bed_assignments` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `admission_id` BIGINT UNSIGNED NOT NULL,
            `bed_id` INT UNSIGNED NOT NULL,
            `assigned_from` DATETIME NOT NULL,
            `assigned_to` DATETIME NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `notes` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_bed_assign_admission` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_bed_assign_bed` FOREIGN KEY (`bed_id`) REFERENCES `beds` (`id`),
            INDEX `idx_bed_assign_active` (`is_active`, `bed_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 5. nursing_notes table
        $db->query("CREATE TABLE IF NOT EXISTS `nursing_notes` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `admission_id` BIGINT UNSIGNED NOT NULL,
            `nurse_id` BIGINT UNSIGNED NOT NULL,
            `note_type` ENUM('routine', 'shift_handover', 'incident', 'doctor_order_execution') NOT NULL DEFAULT 'routine',
            `note_content` TEXT NOT NULL,
            `recorded_at` DATETIME NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_nn_admission` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_nn_nurse` FOREIGN KEY (`nurse_id`) REFERENCES `users` (`id`),
            INDEX `idx_nn_admission` (`admission_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 6. nursing_care_plans table
        $db->query("CREATE TABLE IF NOT EXISTS `nursing_care_plans` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `admission_id` BIGINT UNSIGNED NOT NULL,
            `nursing_diagnosis` VARCHAR(255) NOT NULL,
            `goals` TEXT NOT NULL,
            `interventions` TEXT NOT NULL,
            `evaluation` TEXT NULL,
            `status` ENUM('active', 'completed', 'discontinued') NOT NULL DEFAULT 'active',
            `created_by` BIGINT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_ncp_admission` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_ncp_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
            INDEX `idx_ncp_admission` (`admission_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 7. medication_administrations table (MAR)
        $db->query("CREATE TABLE IF NOT EXISTS `medication_administrations` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `admission_id` BIGINT UNSIGNED NOT NULL,
            `medicine_name` VARCHAR(150) NOT NULL,
            `dosage` VARCHAR(100) NOT NULL,
            `route` VARCHAR(50) NOT NULL DEFAULT 'oral',
            `scheduled_time` DATETIME NOT NULL,
            `administered_time` DATETIME NULL,
            `administered_by` BIGINT UNSIGNED NULL,
            `status` ENUM('pending', 'given', 'missed', 'refused', 'held') NOT NULL DEFAULT 'pending',
            `notes` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_mar_admission` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_mar_nurse` FOREIGN KEY (`administered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
            INDEX `idx_mar_admission` (`admission_id`),
            INDEX `idx_mar_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 8. discharge_records table
        $db->query("CREATE TABLE IF NOT EXISTS `discharge_records` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `admission_id` BIGINT UNSIGNED NOT NULL UNIQUE,
            `discharge_type` ENUM('normal', 'referral', 'against_medical_advice', 'deceased') NOT NULL DEFAULT 'normal',
            `discharge_summary` TEXT NOT NULL,
            `discharge_condition` ENUM('recovered', 'improved', 'unchanged', 'worse') NOT NULL DEFAULT 'improved',
            `discharge_doctor_id` BIGINT UNSIGNED NOT NULL,
            `discharged_at` DATETIME NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_disch_admission` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_disch_doctor` FOREIGN KEY (`discharge_doctor_id`) REFERENCES `doctors` (`id`),
            INDEX `idx_disch_admission` (`admission_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(): void
    {
        $db = \App\Core\Database::getInstance();
        $db->query("DROP TABLE IF EXISTS `discharge_records`;");
        $db->query("DROP TABLE IF EXISTS `medication_administrations`;");
        $db->query("DROP TABLE IF EXISTS `nursing_care_plans`;");
        $db->query("DROP TABLE IF EXISTS `nursing_notes`;");
        $db->query("DROP TABLE IF EXISTS `bed_assignments`;");
        $db->query("DROP TABLE IF EXISTS `admissions`;");
        $db->query("DROP TABLE IF EXISTS `triage_assessments`;");
        $db->query("DROP TABLE IF EXISTS `emergency_cases`;");
    }
}
