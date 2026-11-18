<?php

class CreatePatientTables
{
    public function up(): void
    {
        $db = \App\Core\Database::getInstance();

        // 1. patients table
        $db->query("CREATE TABLE IF NOT EXISTS `patients` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `mrn` VARCHAR(30) NOT NULL UNIQUE,
            `national_id` VARCHAR(30) NULL UNIQUE,
            `first_name` VARCHAR(50) NOT NULL,
            `second_name` VARCHAR(50) NOT NULL,
            `third_name` VARCHAR(50) NULL,
            `last_name` VARCHAR(50) NOT NULL,
            `gender` ENUM('male', 'female') NOT NULL,
            `date_of_birth` DATE NOT NULL,
            `blood_group` ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'unknown') NOT NULL DEFAULT 'unknown',
            `marital_status` ENUM('single', 'married', 'divorced', 'widowed') NULL,
            `phone_primary` VARCHAR(25) NOT NULL,
            `phone_secondary` VARCHAR(25) NULL,
            `address_state` VARCHAR(50) NOT NULL,
            `address_city` VARCHAR(50) NOT NULL,
            `emergency_contact_name` VARCHAR(100) NOT NULL,
            `emergency_contact_phone` VARCHAR(25) NOT NULL,
            `emergency_contact_relation` VARCHAR(50) NULL,
            `is_deceased` TINYINT(1) NOT NULL DEFAULT 0,
            `deceased_date` DATETIME NULL,
            `created_by` BIGINT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_patients_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
            INDEX `idx_patients_mrn` (`mrn`),
            INDEX `idx_patients_search` (`last_name`, `first_name`, `phone_primary`),
            INDEX `idx_patients_national_id` (`national_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. patient_contacts table
        $db->query("CREATE TABLE IF NOT EXISTS `patient_contacts` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `patient_id` BIGINT UNSIGNED NOT NULL,
            `contact_name` VARCHAR(100) NOT NULL,
            `relationship` VARCHAR(50) NOT NULL,
            `phone` VARCHAR(25) NOT NULL,
            `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_patient_contacts_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. patient_allergies table
        $db->query("CREATE TABLE IF NOT EXISTS `patient_allergies` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `patient_id` BIGINT UNSIGNED NOT NULL,
            `allergen_type` ENUM('drug', 'food', 'environmental', 'other') NOT NULL,
            `allergen_name` VARCHAR(150) NOT NULL,
            `severity` ENUM('mild', 'moderate', 'severe', 'life_threatening') NOT NULL,
            `reaction_notes` TEXT NULL,
            `recorded_by` BIGINT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_patient_allergies_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_patient_allergies_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`),
            INDEX `idx_allergies_patient` (`patient_id`, `allergen_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. patient_conditions table
        $db->query("CREATE TABLE IF NOT EXISTS `patient_conditions` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `patient_id` BIGINT UNSIGNED NOT NULL,
            `condition_name` VARCHAR(150) NOT NULL,
            `icd10_code` VARCHAR(15) NULL,
            `diagnosed_date` DATE NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_patient_conditions_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
            INDEX `idx_conditions_patient` (`patient_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 5. appointments table
        $db->query("CREATE TABLE IF NOT EXISTS `appointments` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `patient_id` BIGINT UNSIGNED NOT NULL,
            `doctor_id` BIGINT UNSIGNED NOT NULL,
            `clinic_id` INT UNSIGNED NOT NULL,
            `appointment_date` DATE NOT NULL,
            `appointment_time` TIME NOT NULL,
            `token_number` SMALLINT UNSIGNED NOT NULL,
            `status` ENUM('booked', 'confirmed', 'checked_in', 'in_consultation', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'booked',
            `cancellation_reason` TEXT NULL,
            `created_by` BIGINT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_appointments_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
            CONSTRAINT `fk_appointments_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`),
            CONSTRAINT `fk_appointments_clinic` FOREIGN KEY (`clinic_id`) REFERENCES `clinics` (`id`),
            CONSTRAINT `fk_appointments_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
            INDEX `idx_appointments_lookup` (`doctor_id`, `appointment_date`, `status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 6. visits table
        $db->query("CREATE TABLE IF NOT EXISTS `visits` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `visit_number` VARCHAR(30) NOT NULL UNIQUE,
            `patient_id` BIGINT UNSIGNED NOT NULL,
            `visit_type` ENUM('opd', 'er', 'ipd') NOT NULL DEFAULT 'opd',
            `department_id` INT UNSIGNED NOT NULL,
            `attending_doctor_id` BIGINT UNSIGNED NULL,
            `arrival_time` DATETIME NOT NULL,
            `discharge_time` DATETIME NULL,
            `status` ENUM('active', 'admitted', 'discharged', 'transferred', 'cancelled') NOT NULL DEFAULT 'active',
            `created_by` BIGINT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_visits_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
            CONSTRAINT `fk_visits_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
            CONSTRAINT `fk_visits_doctor` FOREIGN KEY (`attending_doctor_id`) REFERENCES `doctors` (`id`),
            CONSTRAINT `fk_visits_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
            INDEX `idx_visits_status` (`patient_id`, `status`),
            INDEX `idx_visits_arrival` (`arrival_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 7. queues table
        $db->query("CREATE TABLE IF NOT EXISTS `queues` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `clinic_id` INT UNSIGNED NOT NULL,
            `visit_id` BIGINT UNSIGNED NOT NULL,
            `queue_number` SMALLINT UNSIGNED NOT NULL,
            `called_time` DATETIME NULL,
            `status` ENUM('waiting', 'called', 'in_room', 'completed', 'skipped') NOT NULL DEFAULT 'waiting',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_queues_clinic` FOREIGN KEY (`clinic_id`) REFERENCES `clinics` (`id`),
            CONSTRAINT `fk_queues_visit` FOREIGN KEY (`visit_id`) REFERENCES `visits` (`id`),
            INDEX `idx_queues_active` (`clinic_id`, `status`, `created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 8. referrals table
        $db->query("CREATE TABLE IF NOT EXISTS `referrals` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `patient_id` BIGINT UNSIGNED NOT NULL,
            `referral_source` VARCHAR(150) NOT NULL,
            `referral_reason` TEXT NOT NULL,
            `referred_to_department_id` INT UNSIGNED NOT NULL,
            `referral_date` DATE NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_referrals_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
            CONSTRAINT `fk_referrals_department` FOREIGN KEY (`referred_to_department_id`) REFERENCES `departments` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(): void
    {
        $db = \App\Core\Database::getInstance();
        $db->query("DROP TABLE IF EXISTS `referrals`");
        $db->query("DROP TABLE IF EXISTS `queues`");
        $db->query("DROP TABLE IF EXISTS `visits`");
        $db->query("DROP TABLE IF EXISTS `appointments`");
        $db->query("DROP TABLE IF EXISTS `patient_conditions`");
        $db->query("DROP TABLE IF EXISTS `patient_allergies`");
        $db->query("DROP TABLE IF EXISTS `patient_contacts`");
        $db->query("DROP TABLE IF EXISTS `patients`");
    }
}
