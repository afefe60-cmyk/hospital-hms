<?php

class PharmacyAndInventorySeeder
{
    public function run(): void
    {
        $db = \App\Core\Database::getInstance();

        $admin = $db->fetchOne("SELECT id FROM `users` WHERE `username` = 'admin'");
        $adminId = $admin ? $admin['id'] : 1;

        $p1 = $db->fetchOne("SELECT id FROM `patients` WHERE `mrn` = 'MRN-2026-000001'");
        $p1Id = $p1 ? $p1['id'] : 1;

        // 1. Seed Medicine Categories
        $categories = [
            ['name_ar' => 'المضادات الحيوية', 'name_en' => 'Antibiotics', 'description' => 'مضادات الجراثيم والبكتيريا'],
            ['name_ar' => 'المسكنات وخافضات الحرارة', 'name_en' => 'Analgesics & Antipyretics', 'description' => 'تسكين الآلام وخفض الحمى'],
            ['name_ar' => 'مضادات الملاريا', 'name_en' => 'Antimalarials', 'description' => 'بروتوكول علاج الملاريا الوطني بالسودان'],
            ['name_ar' => 'أدوية السكري والأنسولين', 'name_en' => 'Antidiabetics', 'description' => 'أدوية داء السكري الفموية والأنسولين'],
            ['name_ar' => 'أدوية القلب وضغط الدم', 'name_en' => 'Antihypertensives & Cardiac', 'description' => 'تنظيم ضغط الدم والشرايين'],
            ['name_ar' => 'المحاليل والمستلزمات الوريدية', 'name_en' => 'IV Fluids & Infusions', 'description' => 'محاليل الإرواء والتعويض'],
        ];

        foreach ($categories as $cat) {
            $hasCat = $db->fetchOne("SELECT id FROM `medicine_categories` WHERE `name_en` = :ne", ['ne' => $cat['name_en']]);
            if (!$hasCat) {
                $db->query("INSERT INTO `medicine_categories` (`name_ar`, `name_en`, `description`, `is_active`)
                            VALUES (:name_ar, :name_en, :description, 1)", $cat);
            }
        }

        // 2. Seed Warehouses
        $warehouses = [
            ['code' => 'WH-MAIN', 'name_ar' => 'المستودع الدوائي المركزي', 'name_en' => 'Central Medical Store', 'type' => 'main', 'location' => 'المبنى الرئيسي - الطابق الأرضي'],
            ['code' => 'WH-PHARM', 'name_ar' => 'صيدلية العيادات الخارجية', 'name_en' => 'OPD Pharmacy', 'type' => 'pharmacy', 'location' => 'مجمع العيادات الخارجية'],
            ['code' => 'WH-ER', 'name_ar' => 'صيدلية قسم الطوارئ والحوادث', 'name_en' => 'Emergency Pharmacy', 'type' => 'emergency', 'location' => 'قسم الطوارئ والإصابات'],
        ];

        foreach ($warehouses as $wh) {
            $hasWh = $db->fetchOne("SELECT id FROM `warehouses` WHERE `code` = :code", ['code' => $wh['code']]);
            if (!$hasWh) {
                $db->query("INSERT INTO `warehouses` (`code`, `name_ar`, `name_en`, `type`, `location`, `is_active`)
                            VALUES (:code, :name_ar, :name_en, :type, :location, 1)", $wh);
            }
        }

        $mainWh = $db->fetchOne("SELECT id FROM `warehouses` WHERE `code` = 'WH-MAIN'");
        $mainWhId = $mainWh ? (int)$mainWh['id'] : 1;

        $pharmWh = $db->fetchOne("SELECT id FROM `warehouses` WHERE `code` = 'WH-PHARM'");
        $pharmWhId = $pharmWh ? (int)$pharmWh['id'] : 2;

        // Categories IDs
        $catAnalgesic = $db->fetchOne("SELECT id FROM `medicine_categories` WHERE `name_en` = 'Analgesics & Antipyretics'");
        $catAnalgesicId = $catAnalgesic ? (int)$catAnalgesic['id'] : 1;

        $catMalaria = $db->fetchOne("SELECT id FROM `medicine_categories` WHERE `name_en` = 'Antimalarials'");
        $catMalariaId = $catMalaria ? (int)$catMalaria['id'] : 1;

        $catDiabetes = $db->fetchOne("SELECT id FROM `medicine_categories` WHERE `name_en` = 'Antidiabetics'");
        $catDiabetesId = $catDiabetes ? (int)$catDiabetes['id'] : 1;

        $catAnti = $db->fetchOne("SELECT id FROM `medicine_categories` WHERE `name_en` = 'Antibiotics'");
        $catAntiId = $catAnti ? (int)$catAnti['id'] : 1;

        $catIV = $db->fetchOne("SELECT id FROM `medicine_categories` WHERE `name_en` = 'IV Fluids & Infusions'");
        $catIVId = $catIV ? (int)$catIV['id'] : 1;

        // 3. Seed Medicines
        $medicines = [
            ['category_id' => $catAnalgesicId, 'code' => 'MED-PCM-500', 'commercial_name' => 'Paracetamol 500mg', 'scientific_name' => 'Paracetamol (Acetaminophen)', 'dosage_form' => 'tablet', 'strength' => '500mg', 'unit' => 'strip', 'min_stock' => 100, 'reorder' => 200],
            ['category_id' => $catMalariaId, 'code' => 'MED-COARTEM', 'commercial_name' => 'Coartem 20/120mg', 'scientific_name' => 'Artemether / Lumefantrine', 'dosage_form' => 'tablet', 'strength' => '20/120mg', 'unit' => 'box', 'min_stock' => 50, 'reorder' => 100],
            ['category_id' => $catDiabetesId, 'code' => 'MED-MET-500', 'commercial_name' => 'Glucophage 500mg', 'scientific_name' => 'Metformin Hydrochloride', 'dosage_form' => 'tablet', 'strength' => '500mg', 'unit' => 'strip', 'min_stock' => 50, 'reorder' => 100],
            ['category_id' => $catAntiId, 'code' => 'MED-CEF-1G', 'commercial_name' => 'Rocephin 1g IV/IM', 'scientific_name' => 'Ceftriaxone Sodium', 'dosage_form' => 'injection', 'strength' => '1g', 'unit' => 'vial', 'min_stock' => 40, 'reorder' => 80],
            ['category_id' => $catIVId, 'code' => 'MED-RL-500', 'commercial_name' => 'Ringer Lactate 500ml', 'scientific_name' => 'Sodium Lactate Compound', 'dosage_form' => 'infusion', 'strength' => '500ml', 'unit' => 'bottle', 'min_stock' => 60, 'reorder' => 120],
        ];

        foreach ($medicines as $m) {
            $hasMed = $db->fetchOne("SELECT id FROM `medicines` WHERE `code` = :code", ['code' => $m['code']]);
            if (!$hasMed) {
                $db->query("INSERT INTO `medicines` (`category_id`, `code`, `commercial_name`, `scientific_name`, `dosage_form`, `strength`, `unit`, `minimum_stock_alert`, `reorder_level`, `is_prescription_required`, `is_narcotic`, `is_active`)
                            VALUES (:category_id, :code, :commercial_name, :scientific_name, :dosage_form, :strength, :unit, :min_stock, :reorder, 1, 0, 1)", $m);
            }
        }

        // 4. Seed Batches & FEFO Stock
        $medPcm = $db->fetchOne("SELECT id FROM `medicines` WHERE `code` = 'MED-PCM-500'");
        $medCoartem = $db->fetchOne("SELECT id FROM `medicines` WHERE `code` = 'MED-COARTEM'");
        $medCef = $db->fetchOne("SELECT id FROM `medicines` WHERE `code` = 'MED-CEF-1G'");

        if ($medPcm) {
            $pcmId = (int)$medPcm['id'];
            // Batch 1 (Nearer Expiry - 2027-06)
            $hasB1 = $db->fetchOne("SELECT id FROM `medicine_batches` WHERE `batch_number` = 'PCM-2024-B1'");
            if (!$hasB1) {
                $db->query("INSERT INTO `medicine_batches` (`medicine_id`, `warehouse_id`, `batch_number`, `expiry_date`, `cost_price`, `sale_price`, `quantity_on_hand`, `status`)
                            VALUES (:mid, :wid, 'PCM-2024-B1', '2027-06-30', 800.00, 1200.00, 250, 'active')", [
                    'mid' => $pcmId,
                    'wid' => $pharmWhId,
                ]);
            }

            // Batch 2 (Later Expiry - 2028-12)
            $hasB2 = $db->fetchOne("SELECT id FROM `medicine_batches` WHERE `batch_number` = 'PCM-2025-B2'");
            if (!$hasB2) {
                $db->query("INSERT INTO `medicine_batches` (`medicine_id`, `warehouse_id`, `batch_number`, `expiry_date`, `cost_price`, `sale_price`, `quantity_on_hand`, `status`)
                            VALUES (:mid, :wid, 'PCM-2025-B2', '2028-12-31', 900.00, 1400.00, 500, 'active')", [
                    'mid' => $pcmId,
                    'wid' => $mainWhId,
                ]);
            }
        }

        if ($medCoartem) {
            $coartemId = (int)$medCoartem['id'];
            $hasBCoartem = $db->fetchOne("SELECT id FROM `medicine_batches` WHERE `batch_number` = 'COA-2024-001'");
            if (!$hasBCoartem) {
                $db->query("INSERT INTO `medicine_batches` (`medicine_id`, `warehouse_id`, `batch_number`, `expiry_date`, `cost_price`, `sale_price`, `quantity_on_hand`, `status`)
                            VALUES (:mid, :wid, 'COA-2024-001', '2027-10-31', 3500.00, 5000.00, 180, 'active')", [
                    'mid' => $coartemId,
                    'wid' => $pharmWhId,
                ]);
            }
        }

        if ($medCef) {
            $cefId = (int)$medCef['id'];
            $hasBCef = $db->fetchOne("SELECT id FROM `medicine_batches` WHERE `batch_number` = 'CEF-2024-99'");
            if (!$hasBCef) {
                $db->query("INSERT INTO `medicine_batches` (`medicine_id`, `warehouse_id`, `batch_number`, `expiry_date`, `cost_price`, `sale_price`, `quantity_on_hand`, `status`)
                            VALUES (:mid, :wid, 'CEF-2024-99', '2027-08-31', 4000.00, 6500.00, 95, 'active')", [
                    'mid' => $cefId,
                    'wid' => $pharmWhId,
                ]);
            }
        }

        // 5. Seed Suppliers
        $suppliers = [
            ['code' => 'SUP-NMSF', 'name' => 'الصندوق القومي للإمدادات الطبية (NMSF)', 'contact' => 'د. عصام النور', 'phone' => '+249 183 471000', 'email' => 'info@nmsf.gov.sd', 'address' => 'الخرطوم - شارع الحوادث'],
            ['code' => 'SUP-AMIPHARMA', 'name' => 'شركة أميفارما للصناعات الدوائية المحدودة', 'contact' => 'أ. طارق عبد المنعم', 'phone' => '+249 185 330000', 'email' => 'sales@amipharma.com', 'address' => 'الخرطوم بحري - المنطقة الصناعية'],
        ];

        foreach ($suppliers as $sup) {
            $hasSup = $db->fetchOne("SELECT id FROM `suppliers` WHERE `code` = :code", ['code' => $sup['code']]);
            if (!$hasSup) {
                $db->query("INSERT INTO `suppliers` (`code`, `name`, `contact_person`, `phone`, `email`, `address`, `status`)
                            VALUES (:code, :name, :contact, :phone, :email, :address, 'active')", $sup);
            }
        }

        // 6. Seed Purchase Order
        $sup1 = $db->fetchOne("SELECT id FROM `suppliers` WHERE `code` = 'SUP-NMSF'");
        $sup1Id = $sup1 ? (int)$sup1['id'] : 1;

        $hasPo = $db->fetchOne("SELECT id FROM `purchase_orders` WHERE `po_number` = 'PO-202611-0001'");
        if (!$hasPo) {
            $db->query("INSERT INTO `purchase_orders` (`po_number`, `supplier_id`, `warehouse_id`, `order_date`, `expected_delivery_date`, `total_amount`, `status`, `notes`, `created_by`)
                        VALUES ('PO-202611-0001', :sid, :wid, NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 1 DAY, 850000.00, 'received', 'طلبية أدوية ومحاليل أساسية معتمدة', :uid)", [
                'sid' => $sup1Id,
                'wid' => $mainWhId,
                'uid' => $adminId,
            ]);
            $poId = $db->lastInsertId();

            if ($medPcm) {
                $db->query("INSERT INTO `purchase_order_items` (`purchase_order_id`, `medicine_id`, `quantity_ordered`, `quantity_received`, `unit_cost`, `total_cost`)
                            VALUES (:poid, :mid, 500, 500, 900.00, 450000.00)", [
                    'poid' => $poId,
                    'mid' => $medPcm['id'],
                ]);
            }
        }

        // 7. Seed Completed Pharmacy Dispense
        $hasDsp = $db->fetchOne("SELECT id FROM `pharmacy_dispenses` WHERE `dispense_number` = 'DSP-20261118-0001'");
        if (!$hasDsp) {
            $rx = $db->fetchOne("SELECT id FROM `prescriptions` LIMIT 1");
            $rxId = $rx ? (int)$rx['id'] : null;

            $batchPcm = $db->fetchOne("SELECT id, sale_price FROM `medicine_batches` WHERE `batch_number` = 'PCM-2024-B1'");

            $db->query("INSERT INTO `pharmacy_dispenses` (`dispense_number`, `prescription_id`, `patient_id`, `warehouse_id`, `pharmacist_id`, `total_amount`, `payment_status`, `notes`)
                        VALUES ('DSP-20261118-0001', :rx_id, :pid, :wid, :uid, 2400.00, 'paid', 'صرف العلاج لمريض العيادات مع التوجيه')", [
                'rx_id' => $rxId,
                'pid' => $p1Id,
                'wid' => $pharmWhId,
                'uid' => $adminId,
            ]);
            $dspId = $db->lastInsertId();

            if ($medPcm && $batchPcm) {
                $db->query("INSERT INTO `pharmacy_dispense_items` (`pharmacy_dispense_id`, `medicine_id`, `batch_id`, `quantity`, `unit_price`, `total_price`)
                            VALUES (:did, :mid, :bid, 2, :uprice, 2400.00)", [
                    'did' => $dspId,
                    'mid' => $medPcm['id'],
                    'bid' => $batchPcm['id'],
                    'uprice' => $batchPcm['sale_price'],
                ]);
            }
        }
    }
}
