<?php

use App\Core\Database;

class CreateHrAndPayrollTables
{
    public function up(): void
    {
        $db = Database::getInstance();

        // 1. employee_rosters (Staff shift rosters & duty schedules)
        $db->query("CREATE TABLE IF NOT EXISTS `employee_rosters` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `employee_id` BIGINT UNSIGNED NOT NULL,
            `shift_id` INT UNSIGNED NOT NULL,
            `department_id` INT UNSIGNED NOT NULL,
            `duty_date` DATE NOT NULL,
            `duty_role` VARCHAR(100) NULL,
            `status` ENUM('scheduled', 'attended', 'absent', 'on_leave', 'swapped') NOT NULL DEFAULT 'scheduled',
            `notes` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_roster_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_roster_shift` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_roster_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT,
            UNIQUE KEY `uk_roster_emp_date` (`employee_id`, `duty_date`),
            INDEX `idx_roster_lookup` (`duty_date`, `department_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. employee_leaves (Leave requests & balances)
        $db->query("CREATE TABLE IF NOT EXISTS `employee_leaves` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `employee_id` BIGINT UNSIGNED NOT NULL,
            `leave_type` ENUM('annual', 'sick', 'emergency', 'maternity', 'unpaid') NOT NULL DEFAULT 'annual',
            `start_date` DATE NOT NULL,
            `end_date` DATE NOT NULL,
            `total_days` SMALLINT UNSIGNED NOT NULL,
            `reason` TEXT NOT NULL,
            `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
            `approved_by` BIGINT UNSIGNED NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_leaves_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_leaves_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
            INDEX `idx_leaves_dates` (`employee_id`, `start_date`, `end_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. payroll (Monthly staff salary sheets)
        $db->query("CREATE TABLE IF NOT EXISTS `payroll` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `payroll_number` VARCHAR(30) UNIQUE NOT NULL,
            `employee_id` BIGINT UNSIGNED NOT NULL,
            `salary_month` TINYINT UNSIGNED NOT NULL,
            `salary_year` SMALLINT UNSIGNED NOT NULL,
            `basic_salary` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `allowances` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `deductions` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `net_salary` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `is_paid` TINYINT(1) NOT NULL DEFAULT 0,
            `payment_date` DATE NULL,
            `payment_method` ENUM('cash', 'bankak', 'bank_transfer') NULL DEFAULT 'bankak',
            `transaction_reference` VARCHAR(100) NULL,
            `notes` TEXT NULL,
            `processed_by` BIGINT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_payroll_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_payroll_processor` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
            UNIQUE KEY `uk_payroll_emp_month_year` (`employee_id`, `salary_month`, `salary_year`),
            INDEX `idx_payroll_period` (`salary_year`, `salary_month`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. payroll_items (Allowance & deduction line items)
        $db->query("CREATE TABLE IF NOT EXISTS `payroll_items` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `payroll_id` BIGINT UNSIGNED NOT NULL,
            `type` ENUM('allowance', 'deduction') NOT NULL,
            `title` VARCHAR(100) NOT NULL,
            `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_payroll_items_parent` FOREIGN KEY (`payroll_id`) REFERENCES `payroll` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(): void
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS `payroll_items`");
        $db->query("DROP TABLE IF EXISTS `payroll`");
        $db->query("DROP TABLE IF EXISTS `employee_leaves`");
        $db->query("DROP TABLE IF EXISTS `employee_rosters`");
    }
}
