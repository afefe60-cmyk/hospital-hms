<?php

class CreatePharmacyAndInventoryTables
{
    public function up(): void
    {
        $db = \App\Core\Database::getInstance();

        // 1. medicine_categories
        $db->query("CREATE TABLE IF NOT EXISTS `medicine_categories` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name_ar` VARCHAR(100) NOT NULL,
            `name_en` VARCHAR(100) NOT NULL,
            `description` TEXT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. warehouses
        $db->query("CREATE TABLE IF NOT EXISTS `warehouses` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `code` VARCHAR(20) NOT NULL UNIQUE,
            `name_ar` VARCHAR(100) NOT NULL,
            `name_en` VARCHAR(100) NOT NULL,
            `type` ENUM('main', 'pharmacy', 'emergency', 'store') NOT NULL DEFAULT 'main',
            `location` VARCHAR(150) NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. medicines
        $db->query("CREATE TABLE IF NOT EXISTS `medicines` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `category_id` INT UNSIGNED NOT NULL,
            `code` VARCHAR(30) NOT NULL UNIQUE,
            `scientific_name` VARCHAR(150) NOT NULL,
            `commercial_name` VARCHAR(150) NOT NULL,
            `dosage_form` VARCHAR(50) NOT NULL DEFAULT 'tablet', -- tablet, syrup, injection, drops, ointment, capsule, inhaler
            `strength` VARCHAR(50) NOT NULL,
            `unit` VARCHAR(30) NOT NULL DEFAULT 'strip',
            `minimum_stock_alert` INT UNSIGNED NOT NULL DEFAULT 20,
            `reorder_level` INT UNSIGNED NOT NULL DEFAULT 50,
            `is_prescription_required` TINYINT(1) NOT NULL DEFAULT 1,
            `is_narcotic` TINYINT(1) NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_med_category` FOREIGN KEY (`category_id`) REFERENCES `medicine_categories` (`id`),
            INDEX `idx_med_code` (`code`),
            INDEX `idx_med_names` (`commercial_name`, `scientific_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. medicine_batches (FEFO: First Expired, First Out)
        $db->query("CREATE TABLE IF NOT EXISTS `medicine_batches` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `medicine_id` BIGINT UNSIGNED NOT NULL,
            `warehouse_id` INT UNSIGNED NOT NULL,
            `batch_number` VARCHAR(50) NOT NULL,
            `expiry_date` DATE NOT NULL,
            `cost_price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            `sale_price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            `quantity_on_hand` INT NOT NULL DEFAULT 0,
            `status` ENUM('active', 'expired', 'quarantined', 'depleted') NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_batch_med` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`),
            CONSTRAINT `fk_batch_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
            INDEX `idx_batch_fefo` (`medicine_id`, `warehouse_id`, `status`, `expiry_date`),
            INDEX `idx_batch_expiry` (`expiry_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 5. suppliers
        $db->query("CREATE TABLE IF NOT EXISTS `suppliers` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `code` VARCHAR(20) NOT NULL UNIQUE,
            `name` VARCHAR(150) NOT NULL,
            `contact_person` VARCHAR(100) NULL,
            `phone` VARCHAR(30) NOT NULL,
            `email` VARCHAR(100) NULL,
            `address` VARCHAR(200) NULL,
            `tax_number` VARCHAR(50) NULL,
            `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 6. purchase_orders
        $db->query("CREATE TABLE IF NOT EXISTS `purchase_orders` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `po_number` VARCHAR(30) NOT NULL UNIQUE,
            `supplier_id` INT UNSIGNED NOT NULL,
            `warehouse_id` INT UNSIGNED NOT NULL,
            `order_date` DATE NOT NULL,
            `expected_delivery_date` DATE NULL,
            `total_amount` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
            `status` ENUM('draft', 'approved', 'received', 'cancelled') NOT NULL DEFAULT 'draft',
            `notes` TEXT NULL,
            `created_by` BIGINT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_po_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
            CONSTRAINT `fk_po_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
            CONSTRAINT `fk_po_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
            INDEX `idx_po_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 7. purchase_order_items
        $db->query("CREATE TABLE IF NOT EXISTS `purchase_order_items` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `purchase_order_id` BIGINT UNSIGNED NOT NULL,
            `medicine_id` BIGINT UNSIGNED NOT NULL,
            `quantity_ordered` INT UNSIGNED NOT NULL,
            `quantity_received` INT UNSIGNED NOT NULL DEFAULT 0,
            `unit_cost` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            `total_cost` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
            CONSTRAINT `fk_poi_po` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_poi_med` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`),
            INDEX `idx_poi_po` (`purchase_order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 8. pharmacy_dispenses (Dispensing to patient from prescription or direct)
        $db->query("CREATE TABLE IF NOT EXISTS `pharmacy_dispenses` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `dispense_number` VARCHAR(30) NOT NULL UNIQUE,
            `prescription_id` BIGINT UNSIGNED NULL,
            `patient_id` BIGINT UNSIGNED NOT NULL,
            `warehouse_id` INT UNSIGNED NOT NULL,
            `pharmacist_id` BIGINT UNSIGNED NOT NULL,
            `total_amount` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
            `payment_status` ENUM('paid', 'pending', 'waived', 'insured') NOT NULL DEFAULT 'paid',
            `notes` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_pd_prescription` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_pd_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
            CONSTRAINT `fk_pd_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
            CONSTRAINT `fk_pd_pharmacist` FOREIGN KEY (`pharmacist_id`) REFERENCES `users` (`id`),
            INDEX `idx_pd_prescription` (`prescription_id`),
            INDEX `idx_pd_patient` (`patient_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 9. pharmacy_dispense_items
        $db->query("CREATE TABLE IF NOT EXISTS `pharmacy_dispense_items` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `pharmacy_dispense_id` BIGINT UNSIGNED NOT NULL,
            `medicine_id` BIGINT UNSIGNED NOT NULL,
            `batch_id` BIGINT UNSIGNED NOT NULL,
            `quantity` INT UNSIGNED NOT NULL,
            `unit_price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            `total_price` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
            CONSTRAINT `fk_pdi_dispense` FOREIGN KEY (`pharmacy_dispense_id`) REFERENCES `pharmacy_dispenses` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_pdi_med` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`),
            CONSTRAINT `fk_pdi_batch` FOREIGN KEY (`batch_id`) REFERENCES `medicine_batches` (`id`),
            INDEX `idx_pdi_dispense` (`pharmacy_dispense_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 10. stock_movements (Audit trail for all stock transactions)
        $db->query("CREATE TABLE IF NOT EXISTS `stock_movements` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `movement_number` VARCHAR(30) NOT NULL UNIQUE,
            `movement_type` ENUM('purchase_receipt', 'dispense', 'transfer_in', 'transfer_out', 'adjustment_plus', 'adjustment_minus', 'expired_disposal') NOT NULL,
            `medicine_id` BIGINT UNSIGNED NOT NULL,
            `batch_id` BIGINT UNSIGNED NOT NULL,
            `warehouse_id` INT UNSIGNED NOT NULL,
            `quantity` INT NOT NULL, -- positive for additions, negative for deductions
            `reference_type` VARCHAR(50) NULL,
            `reference_id` BIGINT UNSIGNED NULL,
            `notes` VARCHAR(255) NULL,
            `created_by` BIGINT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_sm_med` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`),
            CONSTRAINT `fk_sm_batch` FOREIGN KEY (`batch_id`) REFERENCES `medicine_batches` (`id`),
            CONSTRAINT `fk_sm_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
            CONSTRAINT `fk_sm_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
            INDEX `idx_sm_type` (`movement_type`),
            INDEX `idx_sm_batch` (`batch_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(): void
    {
        $db = \App\Core\Database::getInstance();
        $db->query("DROP TABLE IF EXISTS `stock_movements`;");
        $db->query("DROP TABLE IF EXISTS `pharmacy_dispense_items`;");
        $db->query("DROP TABLE IF EXISTS `pharmacy_dispenses`;");
        $db->query("DROP TABLE IF EXISTS `purchase_order_items`;");
        $db->query("DROP TABLE IF EXISTS `purchase_orders`;");
        $db->query("DROP TABLE IF EXISTS `suppliers`;");
        $db->query("DROP TABLE IF EXISTS `medicine_batches`;");
        $db->query("DROP TABLE IF EXISTS `medicines`;");
        $db->query("DROP TABLE IF EXISTS `warehouses`;");
        $db->query("DROP TABLE IF EXISTS `medicine_categories`;");
    }
}
