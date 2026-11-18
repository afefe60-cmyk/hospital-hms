<?php

use App\Core\Database;

class FinanceAndBillingSeeder
{
    public function run(): void
    {
        $db = Database::getInstance();

        // 1. Seed Cost Centers
        $costCenters = [
            ['code' => 'CC-ADMIN', 'name_ar' => 'الإدارة العامة والمكتب التنفيذي', 'name_en' => 'General Administration'],
            ['code' => 'CC-OPD', 'name_ar' => 'العيادات الخارجية', 'name_en' => 'Outpatient Department'],
            ['code' => 'CC-IPD', 'name_ar' => 'التنويم الداخلي والأجنحة', 'name_en' => 'Inpatient Wards'],
            ['code' => 'CC-ER', 'name_ar' => 'قسم الطوارئ والحوادث', 'name_en' => 'Emergency Department'],
            ['code' => 'CC-OR', 'name_ar' => 'غرف العمليات والمسارح', 'name_en' => 'Operating Rooms'],
            ['code' => 'CC-ICU', 'name_ar' => 'وحدة العناية المركزة', 'name_en' => 'Intensive Care Unit'],
            ['code' => 'CC-LAB', 'name_ar' => 'المختبر الطبي وبنك الدم', 'name_en' => 'Laboratory & Blood Bank'],
            ['code' => 'CC-RAD', 'name_ar' => 'الأشعة والتصوير الطبي', 'name_en' => 'Radiology & Imaging'],
        ];

        foreach ($costCenters as $cc) {
            $existing = $db->fetchOne("SELECT id FROM cost_centers WHERE code = :code", ['code' => $cc['code']]);
            if (!$existing) {
                $db->query("INSERT INTO cost_centers (code, name_ar, name_en) VALUES (:code, :name_ar, :name_en)", $cc);
            }
        }

        // 2. Seed Chart of Accounts
        $accounts = [
            // Level 1: Main Headings
            ['code' => '1000', 'name_ar' => 'الأصول', 'name_en' => 'Assets', 'parent' => null, 'type' => 'asset', 'is_trans' => 0],
            ['code' => '2000', 'name_ar' => 'الالتزامات', 'name_en' => 'Liabilities', 'parent' => null, 'type' => 'liability', 'is_trans' => 0],
            ['code' => '3000', 'name_ar' => 'حقوق الملكية', 'name_en' => 'Equity', 'parent' => null, 'type' => 'equity', 'is_trans' => 0],
            ['code' => '4000', 'name_ar' => 'الإيرادات الطبية والتشغيلية', 'name_en' => 'Operating Revenues', 'parent' => null, 'type' => 'revenue', 'is_trans' => 0],
            ['code' => '5000', 'name_ar' => 'المصروفات التشغيلية', 'name_en' => 'Operating Expenses', 'parent' => null, 'type' => 'expense', 'is_trans' => 0],

            // Current Assets
            ['code' => '1100', 'name_ar' => 'الأصول المتداولة', 'name_en' => 'Current Assets', 'parent' => '1000', 'type' => 'asset', 'is_trans' => 0],
            ['code' => '1101', 'name_ar' => 'النقدية بالصناديق والخزائن', 'name_en' => 'Cash on Hand', 'parent' => '1100', 'type' => 'asset', 'is_trans' => 1],
            ['code' => '1102', 'name_ar' => 'الحسابات المصرفية (بنك الخرطوم - بنكك)', 'name_en' => 'Bank Accounts - Bankak', 'parent' => '1100', 'type' => 'asset', 'is_trans' => 1],
            ['code' => '1103', 'name_ar' => 'مدينو المرضى (حسابات العملاء)', 'name_en' => 'Accounts Receivable - Patients', 'parent' => '1100', 'type' => 'asset', 'is_trans' => 1],
            ['code' => '1104', 'name_ar' => 'مدينو شركات التأمين الطبي', 'name_en' => 'Accounts Receivable - Insurance', 'parent' => '1100', 'type' => 'asset', 'is_trans' => 1],
            ['code' => '1105', 'name_ar' => 'مخزون الأدوية والمستلزمات الطبية', 'name_en' => 'Medical Inventory', 'parent' => '1100', 'type' => 'asset', 'is_trans' => 1],

            // Liabilities
            ['code' => '2101', 'name_ar' => 'الموردون والدائنون (شركات الأدوية)', 'name_en' => 'Accounts Payable - Suppliers', 'parent' => '2000', 'type' => 'liability', 'is_trans' => 1],

            // Revenues
            ['code' => '4101', 'name_ar' => 'إيرادات الكشف والعيادات الخارجية', 'name_en' => 'OPD Consultation Revenue', 'parent' => '4000', 'type' => 'revenue', 'is_trans' => 1],
            ['code' => '4102', 'name_ar' => 'إيرادات التنويم والإقامة الفندقية', 'name_en' => 'Inpatient Bed Charges Revenue', 'parent' => '4000', 'type' => 'revenue', 'is_trans' => 1],
            ['code' => '4103', 'name_ar' => 'إيرادات التحاليل والمختبرات LIS', 'name_en' => 'Laboratory Revenue', 'parent' => '4000', 'type' => 'revenue', 'is_trans' => 1],
            ['code' => '4104', 'name_ar' => 'إيرادات الأشعة والتصوير الطبي RIS', 'name_en' => 'Radiology Revenue', 'parent' => '4000', 'type' => 'revenue', 'is_trans' => 1],
            ['code' => '4105', 'name_ar' => 'إيرادات العمليات الجراحية والتخدير', 'name_en' => 'Surgery & Anesthesia Revenue', 'parent' => '4000', 'type' => 'revenue', 'is_trans' => 1],
            ['code' => '4106', 'name_ar' => 'إيرادات مبيعات الأدوية والصيدلية', 'name_en' => 'Pharmacy Sales Revenue', 'parent' => '4000', 'type' => 'revenue', 'is_trans' => 1],
            ['code' => '4107', 'name_ar' => 'إيرادات العناية المركزة ICU', 'name_en' => 'ICU Critical Care Revenue', 'parent' => '4000', 'type' => 'revenue', 'is_trans' => 1],
        ];

        foreach ($accounts as $acc) {
            $existing = $db->fetchOne("SELECT id FROM accounts WHERE account_code = :code", ['code' => $acc['code']]);
            $parentId = null;
            if ($acc['parent']) {
                $p = $db->fetchOne("SELECT id FROM accounts WHERE account_code = :code", ['code' => $acc['parent']]);
                if ($p) $parentId = $p['id'];
            }

            if (!$existing) {
                $db->query("INSERT INTO accounts (account_code, account_name_ar, account_name_en, parent_id, account_type, is_transactional)
                            VALUES (:code, :name_ar, :name_en, :parent_id, :type, :is_trans)", [
                    'code' => $acc['code'],
                    'name_ar' => $acc['name_ar'],
                    'name_en' => $acc['name_en'],
                    'parent_id' => $parentId,
                    'type' => $acc['type'],
                    'is_trans' => $acc['is_trans'],
                ]);
            }
        }

        // Get revenue accounts
        $accOpd = $db->fetchOne("SELECT id FROM accounts WHERE account_code = '4101'");
        $accIpd = $db->fetchOne("SELECT id FROM accounts WHERE account_code = '4102'");
        $accLab = $db->fetchOne("SELECT id FROM accounts WHERE account_code = '4103'");
        $accRad = $db->fetchOne("SELECT id FROM accounts WHERE account_code = '4104'");
        $accSur = $db->fetchOne("SELECT id FROM accounts WHERE account_code = '4105'");
        $accIcu = $db->fetchOne("SELECT id FROM accounts WHERE account_code = '4107'");
        $accCash = $db->fetchOne("SELECT id FROM accounts WHERE account_code = '1101'");

        // Get Departments
        $depMed = $db->fetchOne("SELECT id FROM departments WHERE code = 'DEP-MED'") ?: $db->fetchOne("SELECT id FROM departments LIMIT 1");
        $depSurg = $db->fetchOne("SELECT id FROM departments WHERE code = 'DEP-SURG'") ?: $depMed;
        $depEr = $db->fetchOne("SELECT id FROM departments WHERE code = 'DEP-ER'") ?: $depMed;

        // 3. Seed Service Catalog
        $services = [
            ['code' => 'SRV-CONS-GEN', 'name_ar' => 'كشف واستشارة طبية عامة', 'name_en' => 'General OPD Consultation', 'dep' => $depMed['id'], 'acc' => $accOpd ? $accOpd['id'] : null, 'price' => 15000.00],
            ['code' => 'SRV-CONS-SPEC', 'name_ar' => 'كشف ومناظرة استشاري / أخصائي', 'name_en' => 'Specialist Consultation', 'dep' => $depMed['id'], 'acc' => $accOpd ? $accOpd['id'] : null, 'price' => 25000.00],
            ['code' => 'SRV-EMR-TRIAGE', 'name_ar' => 'فحص وتقييم طوارئ وفرز سريري', 'name_en' => 'ER Triage & Assessment', 'dep' => $depEr['id'], 'acc' => $accOpd ? $accOpd['id'] : null, 'price' => 10000.00],
            ['code' => 'SRV-BED-STD', 'name_ar' => 'إقامة وتنويم سرير عنبر عام (يومي)', 'name_en' => 'Standard Inpatient Bed Day', 'dep' => $depMed['id'], 'acc' => $accIpd ? $accIpd['id'] : null, 'price' => 25000.00],
            ['code' => 'SRV-BED-ICU', 'name_ar' => 'إقامة سرير عناية مركزة شاملة المراقبة (يومي)', 'name_en' => 'ICU Critical Care Bed Day', 'dep' => $depSurg['id'], 'acc' => $accIcu ? $accIcu['id'] : null, 'price' => 75000.00],
            ['code' => 'SRV-SURG-LAP-CHOL', 'name_ar' => 'عملية استئصال المرارة بالمنظار', 'name_en' => 'Laparoscopic Cholecystectomy', 'dep' => $depSurg['id'], 'acc' => $accSur ? $accSur['id'] : null, 'price' => 350000.00],
            ['code' => 'SRV-SURG-APP', 'name_ar' => 'عملية استئصال الزائدة الدودية الطارئة', 'name_en' => 'Emergency Appendectomy', 'dep' => $depSurg['id'], 'acc' => $accSur ? $accSur['id'] : null, 'price' => 200000.00],
            ['code' => 'SRV-LAB-CBC', 'name_ar' => 'تحليل صورة الدم الكاملة CBC', 'name_en' => 'Complete Blood Count (CBC)', 'dep' => $depMed['id'], 'acc' => $accLab ? $accLab['id'] : null, 'price' => 8000.00],
            ['code' => 'SRV-LAB-MAL', 'name_ar' => 'فحص شريحة ملاريا دموية سميكة وخفيفة', 'name_en' => 'Malaria Blood Film', 'dep' => $depMed['id'], 'acc' => $accLab ? $accLab['id'] : null, 'price' => 4000.00],
            ['code' => 'SRV-RAD-CXR', 'name_ar' => 'تصوير أشعة سينية للصدر CXR', 'name_en' => 'Chest X-Ray PA', 'dep' => $depMed['id'], 'acc' => $accRad ? $accRad['id'] : null, 'price' => 12000.00],
            ['code' => 'SRV-RAD-US-ABD', 'name_ar' => 'موجات صوتية للبطن والحوض US', 'name_en' => 'Abdominal & Pelvic Ultrasound', 'dep' => $depMed['id'], 'acc' => $accRad ? $accRad['id'] : null, 'price' => 20000.00],
        ];

        foreach ($services as $srv) {
            $existing = $db->fetchOne("SELECT id FROM service_catalog WHERE code = :code", ['code' => $srv['code']]);
            if (!$existing) {
                $db->query("INSERT INTO service_catalog (code, name_ar, name_en, department_id, revenue_account_id, price_standard, is_active)
                            VALUES (:code, :name_ar, :name_en, :dep, :acc, :price, 1)", [
                    'code' => $srv['code'],
                    'name_ar' => $srv['name_ar'],
                    'name_en' => $srv['name_en'],
                    'dep' => $srv['dep'],
                    'acc' => $srv['acc'],
                    'price' => $srv['price'],
                ]);
            }
        }

        // 4. Seed Cashboxes
        $cashboxes = [
            ['code' => 'CASH-REC', 'name_ar' => 'صندوق الاستقبال والعيادات الخارجية', 'name_en' => 'Reception & Clinics Cashbox', 'acc' => $accCash ? $accCash['id'] : null],
            ['code' => 'CASH-PHARM', 'name_ar' => 'صندوق الصيدلية المركزية', 'name_en' => 'Central Pharmacy Cashbox', 'acc' => $accCash ? $accCash['id'] : null],
            ['code' => 'CASH-ER', 'name_ar' => 'صندوق الطوارئ والإصابات 24 ساعة', 'name_en' => 'Emergency Cashbox 24/7', 'acc' => $accCash ? $accCash['id'] : null],
        ];

        foreach ($cashboxes as $cb) {
            $existing = $db->fetchOne("SELECT id FROM cashboxes WHERE code = :code", ['code' => $cb['code']]);
            if (!$existing) {
                $db->query("INSERT INTO cashboxes (code, name_ar, name_en, account_id, is_active)
                            VALUES (:code, :name_ar, :name_en, :acc, 1)", [
                    'code' => $cb['code'],
                    'name_ar' => $cb['name_ar'],
                    'name_en' => $cb['name_en'],
                    'acc' => $cb['acc'],
                ]);
            }
        }

        // 5. Seed Open Cashbox Session
        $adminUser = $db->fetchOne("SELECT id FROM users WHERE username = 'admin'");
        $adminUserId = $adminUser ? (int)$adminUser['id'] : 1;
        $boxRec = $db->fetchOne("SELECT id FROM cashboxes WHERE code = 'CASH-REC'");

        if ($boxRec) {
            $existingSess = $db->fetchOne("SELECT id FROM cashbox_sessions WHERE session_number = 'SESS-20261118-0001'");
            if (!$existingSess) {
                $db->query("INSERT INTO cashbox_sessions (
                    session_number, cashbox_id, cashier_user_id, opened_at, opening_balance, status, notes
                ) VALUES (
                    'SESS-20261118-0001', :box_id, :uid, '2026-11-18 08:00:00', 50000.00, 'open', 'وردية الفترة الصباحية - صندوق الاستقبال'
                )", [
                    'box_id' => $boxRec['id'],
                    'uid' => $adminUserId,
                ]);
                $sessId = (int)$db->lastInsertId();
            } else {
                $sessId = (int)$existingSess['id'];
            }
        } else {
            $sessId = null;
        }

        // 6. Seed Sample Invoice & Payment
        $patient = $db->fetchOne("SELECT id FROM patients ORDER BY id ASC LIMIT 1");
        $patientId = $patient ? (int)$patient['id'] : 1;

        $existingInv = $db->fetchOne("SELECT id FROM invoices WHERE invoice_number = 'INV-202611-00001'");
        if (!$existingInv) {
            $db->query("INSERT INTO invoices (
                invoice_number, patient_id, total_gross, total_discount, total_insurance,
                total_patient_due, paid_amount, status, created_by, notes
            ) VALUES (
                'INV-202611-00001', :pid, 37000.00, 0.00, 0.00,
                37000.00, 37000.00, 'paid', :uid, 'فاتورة خدمات كشف باطنية وتحاليل معملية'
            )", [
                'pid' => $patientId,
                'uid' => $adminUserId,
            ]);
            $invId = (int)$db->lastInsertId();

            // Insert invoice items
            $s1 = $db->fetchOne("SELECT id FROM service_catalog WHERE code = 'SRV-CONS-SPEC'");
            $s2 = $db->fetchOne("SELECT id FROM service_catalog WHERE code = 'SRV-LAB-CBC'");
            $s3 = $db->fetchOne("SELECT id FROM service_catalog WHERE code = 'SRV-LAB-MAL'");

            $items = [
                ['service_id' => $s1 ? $s1['id'] : null, 'type' => 'service', 'desc' => 'كشف ومناظرة استشاري باطنية', 'qty' => 1, 'price' => 25000.00, 'total' => 25000.00],
                ['service_id' => $s2 ? $s2['id'] : null, 'type' => 'lab_test', 'desc' => 'تحليل صورة الدم الكاملة CBC', 'qty' => 1, 'price' => 8000.00, 'total' => 8000.00],
                ['service_id' => $s3 ? $s3['id'] : null, 'type' => 'lab_test', 'desc' => 'فحص شريحة ملاريا دموية سميكة وخفيفة', 'qty' => 1, 'price' => 4000.00, 'total' => 4000.00],
            ];

            foreach ($items as $it) {
                $db->query("INSERT INTO invoice_items (invoice_id, service_id, item_type, description, quantity, unit_price, total_amount)
                            VALUES (:inv_id, :srv_id, :type, :desc, :qty, :price, :total)", [
                    'inv_id' => $invId,
                    'srv_id' => $it['service_id'],
                    'type' => $it['type'],
                    'desc' => $it['desc'],
                    'qty' => $it['qty'],
                    'price' => $it['price'],
                    'total' => $it['total'],
                ]);
            }

            // Seed Payment
            $db->query("INSERT INTO payments (
                receipt_number, invoice_id, session_id, amount, payment_method, transaction_reference, notes, collected_by
            ) VALUES (
                'RCP-202611-00001', :inv_id, :sid, 37000.00, 'bankak', 'TRX-BOC-9988221', 'سداد كامل عبر تطبيق بنكك', :uid
            )", [
                'inv_id' => $invId,
                'sid' => $sessId,
                'uid' => $adminUserId,
            ]);
        }
    }
}
