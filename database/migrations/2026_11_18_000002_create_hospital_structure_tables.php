<?php

class CreateHospitalStructureTables
{
    public function up(): void
    {
        $db = \App\Core\Database::getInstance();

        // 1. departments
        $db->query("CREATE TABLE IF NOT EXISTS `departments` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `code` VARCHAR(20) NOT NULL UNIQUE,
            `name_ar` VARCHAR(100) NOT NULL,
            `name_en` VARCHAR(100) NOT NULL,
            `type` ENUM('medical', 'diagnostic', 'pharmaceutical', 'administrative', 'financial') NOT NULL DEFAULT 'medical',
            `head_employee_id` BIGINT UNSIGNED NULL,
            `cost_center_id` INT UNSIGNED NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_departments_type` (`type`),
            INDEX `idx_departments_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. specialties
        $db->query("CREATE TABLE IF NOT EXISTS `specialties` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `code` VARCHAR(20) NOT NULL UNIQUE,
            `name_ar` VARCHAR(100) NOT NULL,
            `name_en` VARCHAR(100) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. employees
        $db->query("CREATE TABLE IF NOT EXISTS `employees` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `employee_code` VARCHAR(30) NOT NULL UNIQUE,
            `first_name` VARCHAR(50) NOT NULL,
            `second_name` VARCHAR(50) NOT NULL,
            `last_name` VARCHAR(50) NOT NULL,
            `gender` ENUM('male', 'female') NOT NULL,
            `national_id` VARCHAR(30) NOT NULL UNIQUE,
            `phone` VARCHAR(25) NOT NULL,
            `email` VARCHAR(100) NULL,
            `department_id` INT UNSIGNED NOT NULL,
            `job_title` VARCHAR(100) NOT NULL,
            `hire_date` DATE NOT NULL,
            `basic_salary` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
            `status` ENUM('active', 'on_leave', 'resigned', 'terminated') NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_employees_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
            INDEX `idx_employees_national_id` (`national_id`),
            INDEX `idx_employees_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. clinics
        $db->query("CREATE TABLE IF NOT EXISTS `clinics` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `department_id` INT UNSIGNED NOT NULL,
            `room_number` VARCHAR(20) NOT NULL,
            `name_ar` VARCHAR(100) NOT NULL,
            `name_en` VARCHAR(100) NOT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_clinics_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 5. doctors
        $db->query("CREATE TABLE IF NOT EXISTS `doctors` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `employee_id` BIGINT UNSIGNED NOT NULL UNIQUE,
            `specialty_id` INT UNSIGNED NOT NULL,
            `medical_license_number` VARCHAR(50) NOT NULL UNIQUE,
            `degree_title` VARCHAR(100) NOT NULL,
            `consultation_fee` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            `commission_percentage` DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_doctors_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_doctors_specialty` FOREIGN KEY (`specialty_id`) REFERENCES `specialties` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 6. nurses
        $db->query("CREATE TABLE IF NOT EXISTS `nurses` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `employee_id` BIGINT UNSIGNED NOT NULL UNIQUE,
            `nursing_license_number` VARCHAR(50) NOT NULL UNIQUE,
            `qualification` VARCHAR(100) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_nurses_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 7. shifts
        $db->query("CREATE TABLE IF NOT EXISTS `shifts` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name_ar` VARCHAR(50) NOT NULL,
            `name_en` VARCHAR(50) NOT NULL,
            `start_time` TIME NOT NULL,
            `end_time` TIME NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 8. doctor_schedules
        $db->query("CREATE TABLE IF NOT EXISTS `doctor_schedules` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `doctor_id` BIGINT UNSIGNED NOT NULL,
            `clinic_id` INT UNSIGNED NOT NULL,
            `day_of_week` TINYINT UNSIGNED NOT NULL,
            `start_time` TIME NOT NULL,
            `end_time` TIME NOT NULL,
            `max_patients` SMALLINT UNSIGNED NOT NULL DEFAULT 30,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_doctor_schedules_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_doctor_schedules_clinic` FOREIGN KEY (`clinic_id`) REFERENCES `clinics` (`id`) ON DELETE CASCADE,
            INDEX `idx_schedules_lookup` (`doctor_id`, `day_of_week`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 9. wards
        $db->query("CREATE TABLE IF NOT EXISTS `wards` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `department_id` INT UNSIGNED NOT NULL,
            `name_ar` VARCHAR(100) NOT NULL,
            `name_en` VARCHAR(100) NOT NULL,
            `gender_type` ENUM('male', 'female', 'mixed', 'pediatric') NOT NULL DEFAULT 'mixed',
            `floor_number` VARCHAR(10) NOT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_wards_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 10. rooms
        $db->query("CREATE TABLE IF NOT EXISTS `rooms` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `ward_id` INT UNSIGNED NOT NULL,
            `room_number` VARCHAR(20) NOT NULL,
            `room_type` ENUM('standard', 'semi_private', 'private', 'vip', 'isolation') NOT NULL DEFAULT 'standard',
            `daily_rate` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_rooms_ward` FOREIGN KEY (`ward_id`) REFERENCES `wards` (`id`) ON DELETE CASCADE,
            INDEX `idx_rooms_ward` (`ward_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 11. beds
        $db->query("CREATE TABLE IF NOT EXISTS `beds` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `room_id` INT UNSIGNED NOT NULL,
            `bed_number` VARCHAR(20) NOT NULL,
            `status` ENUM('available', 'occupied', 'cleaning', 'maintenance', 'reserved') NOT NULL DEFAULT 'available',
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_beds_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
            INDEX `idx_beds_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 12. operating_rooms
        $db->query("CREATE TABLE IF NOT EXISTS `operating_rooms` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name_ar` VARCHAR(100) NOT NULL,
            `name_en` VARCHAR(100) NOT NULL,
            `status` ENUM('available', 'in_surgery', 'cleaning', 'maintenance') NOT NULL DEFAULT 'available',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(): void
    {
        $db = \App\Core\Database::getInstance();
        $db->query("DROP TABLE IF EXISTS `operating_rooms`");
        $db->query("DROP TABLE IF EXISTS `beds`");
        $db->query("DROP TABLE IF EXISTS `rooms`");
        $db->query("DROP TABLE IF EXISTS `wards`");
        $db->query("DROP TABLE IF EXISTS `doctor_schedules`");
        $db->query("DROP TABLE IF EXISTS `shifts`");
        $db->query("DROP TABLE IF EXISTS `nurses`");
        $db->query("DROP TABLE IF EXISTS `doctors`");
        $db->query("DROP TABLE IF EXISTS `clinics`");
        $db->query("DROP TABLE IF EXISTS `employees`");
        $db->query("DROP TABLE IF EXISTS `specialties`");
        $db->query("DROP TABLE IF EXISTS `departments`");
    }
}
