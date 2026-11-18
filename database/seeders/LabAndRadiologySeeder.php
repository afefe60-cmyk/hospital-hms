<?php

class LabAndRadiologySeeder
{
    public function run(): void
    {
        $db = \App\Core\Database::getInstance();

        $admin = $db->fetchOne("SELECT id FROM `users` WHERE `username` = 'admin'");
        $adminId = $admin ? $admin['id'] : 1;

        $doc = $db->fetchOne("SELECT id FROM `doctors` LIMIT 1");
        $docId = $doc ? $doc['id'] : 1;

        $p1 = $db->fetchOne("SELECT id FROM `patients` WHERE `mrn` = 'MRN-2026-000001'");
        $p1Id = $p1 ? $p1['id'] : 1;

        // 1. Seed Lab Tests Catalog
        $tests = [
            ['code' => 'LAB-CBC', 'name_en' => 'Complete Blood Count (CBC)', 'name_ar' => 'صورة دم كاملة (CBC)', 'category' => 'hematology', 'sample_type' => 'whole_blood', 'price' => 15000.00, 'normal_range' => 'Standard Hematology', 'unit' => 'Mixed', 'turnaround_time_hours' => 2],
            ['code' => 'LAB-MAL-BF', 'name_en' => 'Malaria Blood Film & ICT', 'name_ar' => 'فحص ملاريا بالدم وفيلم رقيق وكثيف', 'category' => 'parasitology', 'sample_type' => 'whole_blood', 'price' => 8000.00, 'normal_range' => 'Negative', 'unit' => 'Qualitative', 'turnaround_time_hours' => 1],
            ['code' => 'LAB-WIDAL', 'name_en' => 'Widal Test for Typhoid', 'name_ar' => 'فحص التيفوئيد (تفاعل فيدال)', 'category' => 'serology', 'sample_type' => 'serum', 'price' => 7000.00, 'normal_range' => 'Titre < 1:80', 'unit' => 'Titre', 'turnaround_time_hours' => 2],
            ['code' => 'LAB-FBS', 'name_en' => 'Fasting Blood Sugar (FBS)', 'name_ar' => 'سكر الدم الصائم', 'category' => 'biochemistry', 'sample_type' => 'serum', 'price' => 5000.00, 'normal_range' => '70 - 110', 'unit' => 'mg/dL', 'turnaround_time_hours' => 1],
            ['code' => 'LAB-RBS', 'name_en' => 'Random Blood Sugar (RBS)', 'name_ar' => 'سكر الدم العشوائي', 'category' => 'biochemistry', 'sample_type' => 'serum', 'price' => 5000.00, 'normal_range' => '80 - 140', 'unit' => 'mg/dL', 'turnaround_time_hours' => 1],
            ['code' => 'LAB-CREAT', 'name_en' => 'Serum Creatinine (Renal Function)', 'name_ar' => 'وظائف الكلى - كرياتينين المصل', 'category' => 'biochemistry', 'sample_type' => 'serum', 'price' => 9000.00, 'normal_range' => '0.6 - 1.2', 'unit' => 'mg/dL', 'turnaround_time_hours' => 2],
            ['code' => 'LAB-BILIRUBIN', 'name_en' => 'Total Serum Bilirubin', 'name_ar' => 'الصفراء الكلية - بيليروبين المصل', 'category' => 'biochemistry', 'sample_type' => 'serum', 'price' => 8500.00, 'normal_range' => '0.2 - 1.0', 'unit' => 'mg/dL', 'turnaround_time_hours' => 2],
            ['code' => 'LAB-URINE-GEN', 'name_en' => 'General Urine Examination (GUE)', 'name_ar' => 'تحليل البول الكامل الروتيني', 'category' => 'urine', 'sample_type' => 'urine', 'price' => 6000.00, 'normal_range' => 'Clear, Nil Alb/Sug', 'unit' => 'Microscopic', 'turnaround_time_hours' => 1],
            ['code' => 'LAB-STOOL-GEN', 'name_en' => 'General Stool Examination', 'name_ar' => 'تحليل البراز والطفيليات', 'category' => 'parasitology', 'sample_type' => 'stool', 'price' => 6000.00, 'normal_range' => 'Nil parasites/ova', 'unit' => 'Microscopic', 'turnaround_time_hours' => 1],
            ['code' => 'LAB-HBA1C', 'name_en' => 'Glycated Hemoglobin (HbA1c)', 'name_ar' => 'فحص السكر التراكمي (الهيموجلوبين السكري)', 'category' => 'biochemistry', 'sample_type' => 'whole_blood', 'price' => 20000.00, 'normal_range' => '4.5 - 5.7', 'unit' => '%', 'turnaround_time_hours' => 3],
        ];

        foreach ($tests as $t) {
            $existing = $db->fetchOne("SELECT id FROM `lab_tests` WHERE `code` = :code", ['code' => $t['code']]);
            if (!$existing) {
                $db->query("INSERT INTO `lab_tests` (`code`, `name_en`, `name_ar`, `category`, `sample_type`, `price`, `normal_range`, `unit`, `turnaround_time_hours`, `is_active`)
                            VALUES (:code, :name_en, :name_ar, :category, :sample_type, :price, :normal_range, :unit, :turnaround_time_hours, 1)", $t);
            }
        }

        // 2. Seed Radiology Services Catalog
        $radServices = [
            ['code' => 'RAD-CXR-PA', 'name_en' => 'Chest X-Ray PA View', 'name_ar' => 'أشعة سينية على الصدر (منظر خلفي أمامي)', 'modality' => 'x_ray', 'price' => 18000.00, 'preparation_instructions' => 'خلع الحلي والمعادن والملابس العلوية'],
            ['code' => 'RAD-US-ABD', 'name_en' => 'Abdominal & Pelvic Ultrasound', 'name_ar' => 'موجات صوتية وسونار على البطن والحوض', 'modality' => 'ultrasound', 'price' => 25000.00, 'preparation_instructions' => 'صيام 6 ساعات وشرب لتر ماء قبل الفحص'],
            ['code' => 'RAD-CT-BRAIN', 'name_en' => 'CT Brain Non-Contrast', 'name_ar' => 'أشعة مقطعية على المخ والجمجمة بدون صبغة', 'modality' => 'ct_scan', 'price' => 90000.00, 'preparation_instructions' => 'إزالة أي معادن بالرأس والأسنان'],
            ['code' => 'RAD-KNEE-XRAY', 'name_en' => 'Knee Joint AP & Lateral Views', 'name_ar' => 'أشعة عادية على مفصل الركبة منظرين', 'modality' => 'x_ray', 'price' => 16000.00, 'preparation_instructions' => 'لا يتطلب تحضير خاص'],
            ['code' => 'RAD-ECHO', 'name_en' => 'Transthoracic Echocardiogram (Echo)', 'name_ar' => 'موجات صوتية إيكو على عضلة وصمامات القلب', 'modality' => 'echo', 'price' => 45000.00, 'preparation_instructions' => 'إحضار رسم القلب السابق ومستندات الضغط'],
        ];

        foreach ($radServices as $rs) {
            $existing = $db->fetchOne("SELECT id FROM `radiology_services` WHERE `code` = :code", ['code' => $rs['code']]);
            if (!$existing) {
                $db->query("INSERT INTO `radiology_services` (`code`, `name_en`, `name_ar`, `modality`, `price`, `preparation_instructions`, `is_active`)
                            VALUES (:code, :name_en, :name_ar, :modality, :price, :preparation_instructions, 1)", $rs);
            }
        }

        // 3. Seed Completed Lab Order with Sample and Approved Result
        $labOrd1 = $db->fetchOne("SELECT id FROM `lab_orders` WHERE `order_number` = 'LAB-20261118-0001'");
        if (!$labOrd1) {
            $db->query("INSERT INTO `lab_orders` (`order_number`, `patient_id`, `doctor_id`, `priority`, `clinical_indication`, `status`)
                        VALUES ('LAB-20261118-0001', :pid, :doc_id, 'urgent', 'حمى حادة واشتباه ملاريا وفقر دم', 'completed')", [
                'pid' => $p1Id,
                'doc_id' => $docId,
            ]);
            $labOrd1Id = $db->lastInsertId();
        } else {
            $labOrd1Id = $labOrd1['id'];
        }

        // Link Tests (Malaria + CBC)
        $malariaTest = $db->fetchOne("SELECT id, price FROM `lab_tests` WHERE `code` = 'LAB-MAL-BF'");
        $cbcTest = $db->fetchOne("SELECT id, price FROM `lab_tests` WHERE `code` = 'LAB-CBC'");

        if ($malariaTest) {
            $hasMal = $db->fetchOne("SELECT id FROM `lab_order_items` WHERE `lab_order_id` = :oid AND `lab_test_id` = :tid", ['oid' => $labOrd1Id, 'tid' => $malariaTest['id']]);
            if (!$hasMal) {
                $db->query("INSERT INTO `lab_order_items` (`lab_order_id`, `lab_test_id`, `price`)
                            VALUES (:oid, :tid, :p)", [
                    'oid' => $labOrd1Id,
                    'tid' => $malariaTest['id'],
                    'p' => $malariaTest['price'],
                ]);
            }
        }

        if ($cbcTest) {
            $hasCbc = $db->fetchOne("SELECT id FROM `lab_order_items` WHERE `lab_order_id` = :oid AND `lab_test_id` = :tid", ['oid' => $labOrd1Id, 'tid' => $cbcTest['id']]);
            if (!$hasCbc) {
                $db->query("INSERT INTO `lab_order_items` (`lab_order_id`, `lab_test_id`, `price`)
                            VALUES (:oid, :tid, :p)", [
                    'oid' => $labOrd1Id,
                    'tid' => $cbcTest['id'],
                    'p' => $cbcTest['price'],
                ]);
            }
        }

        // Seed Sample
        $hasSample = $db->fetchOne("SELECT id FROM `lab_samples` WHERE `lab_order_id` = :oid", ['oid' => $labOrd1Id]);
        if (!$hasSample) {
            $db->query("INSERT INTO `lab_samples` (`lab_order_id`, `sample_barcode`, `sample_type`, `collected_at`, `collected_by`, `status`)
                        VALUES (:oid, 'SMP-20261118-00001', 'whole_blood', NOW() - INTERVAL 3 HOUR, :uid, 'received')", [
                'oid' => $labOrd1Id,
                'uid' => $adminId,
            ]);
        }

        // Seed Approved Result
        $result = $db->fetchOne("SELECT id FROM `lab_results` WHERE `lab_order_id` = :oid", ['oid' => $labOrd1Id]);
        if (!$result) {
            $db->query("INSERT INTO `lab_results` (`lab_order_id`, `verified_by`, `verified_at`, `status`, `general_comments`)
                        VALUES (:oid, :uid, NOW() - INTERVAL 1 HOUR, 'approved', 'تم فحص العينة والتأكد من النتائج كيميائياً ومجهرياً')", [
                'oid' => $labOrd1Id,
                'uid' => $adminId,
            ]);
            $resultId = $db->lastInsertId();
        } else {
            $resultId = $result['id'];
        }

        // Seed Result Items (Malaria + CBC items)
        if ($malariaTest) {
            $hasMalRes = $db->fetchOne("SELECT id FROM `lab_result_items` WHERE `lab_result_id` = :rid AND `lab_test_id` = :tid", ['rid' => $resultId, 'tid' => $malariaTest['id']]);
            if (!$hasMalRes) {
                $db->query("INSERT INTO `lab_result_items` (`lab_result_id`, `lab_test_id`, `parameter_name`, `result_value`, `unit`, `reference_range`, `flag`)
                            VALUES (:rid, :tid, 'Malaria Blood Film (Trophozoites)', 'Positive (++ Plasmodium falciparum)', 'Grade', 'Negative', 'positive')", [
                    'rid' => $resultId,
                    'tid' => $malariaTest['id'],
                ]);
            }
        }

        if ($cbcTest) {
            $cbcItems = [
                ['param' => 'Hemoglobin (Hb)', 'val' => '10.2', 'unit' => 'g/dL', 'range' => '13.0 - 17.0', 'flag' => 'low'],
                ['param' => 'Total WBC Count', 'val' => '12400', 'unit' => '/uL', 'range' => '4000 - 11000', 'flag' => 'high'],
                ['param' => 'Platelet Count', 'val' => '115000', 'unit' => '/uL', 'range' => '150000 - 450000', 'flag' => 'low'],
            ];

            foreach ($cbcItems as $ci) {
                $hasItem = $db->fetchOne("SELECT id FROM `lab_result_items` WHERE `lab_result_id` = :rid AND `parameter_name` = :pname", ['rid' => $resultId, 'pname' => $ci['param']]);
                if (!$hasItem) {
                    $db->query("INSERT INTO `lab_result_items` (`lab_result_id`, `lab_test_id`, `parameter_name`, `result_value`, `unit`, `reference_range`, `flag`)
                                VALUES (:rid, :tid, :pname, :rval, :unit, :range, :flag)", [
                        'rid' => $resultId,
                        'tid' => $cbcTest['id'],
                        'pname' => $ci['param'],
                        'rval' => $ci['val'],
                        'unit' => $ci['unit'],
                        'range' => $ci['range'],
                        'flag' => $ci['flag'],
                    ]);
                }
            }
        }

        // 4. Seed Completed Radiology Order with Report
        $radOrd1 = $db->fetchOne("SELECT id FROM `radiology_orders` WHERE `order_number` = 'RAD-20261118-0001'");
        if (!$radOrd1) {
            $cxr = $db->fetchOne("SELECT id FROM `radiology_services` WHERE `code` = 'RAD-CXR-PA'");
            $cxrId = $cxr ? $cxr['id'] : 1;

            $db->query("INSERT INTO `radiology_orders` (`order_number`, `patient_id`, `doctor_id`, `service_id`, `priority`, `clinical_indication`, `status`)
                        VALUES ('RAD-20261118-0001', :pid, :doc_id, :sid, 'routine', 'سعال مستمر وحمى لاستبعاد التهاب رئوي', 'reported')", [
                'pid' => $p1Id,
                'doc_id' => $docId,
                'sid' => $cxrId,
            ]);
            $radOrd1Id = $db->lastInsertId();

            // Seed Report
            $db->query("INSERT INTO `radiology_reports` (`radiology_order_id`, `radiologist_id`, `findings`, `impression`, `reported_at`, `status`)
                        VALUES (:roid, :doc_id, 'Bilateral lung fields appear clear without consolidation or focal infiltrates. Cardiothoracic ratio is normal. Both costophrenic angles are sharp. Visualized bony thorax is intact.', 'Normal Chest Radiograph (No active parenchymal lesion or pneumonia detected).', NOW() - INTERVAL 1 HOUR, 'final')", [
                'roid' => $radOrd1Id,
                'doc_id' => $docId,
            ]);
        }
    }
}
