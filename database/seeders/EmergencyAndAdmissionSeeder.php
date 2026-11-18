<?php

class EmergencyAndAdmissionSeeder
{
    public function run(): void
    {
        $db = \App\Core\Database::getInstance();

        $admin = $db->fetchOne("SELECT id FROM `users` WHERE `username` = 'admin'");
        $adminId = $admin ? $admin['id'] : 1;

        $doc = $db->fetchOne("SELECT id FROM `doctors` LIMIT 1");
        $docId = $doc ? $doc['id'] : 1;

        $dept = $db->fetchOne("SELECT id FROM `departments` WHERE `code` = 'DEP-ER' LIMIT 1");
        $erDeptId = $dept ? $dept['id'] : 1;

        $internalDept = $db->fetchOne("SELECT id FROM `departments` WHERE `code` = 'DEP-MED' LIMIT 1");
        $intDeptId = $internalDept ? $internalDept['id'] : 1;

        $p1 = $db->fetchOne("SELECT id FROM `patients` WHERE `mrn` = 'MRN-2026-000001'");
        $p1Id = $p1 ? $p1['id'] : 1;

        // 1. Seed Emergency Case 1 (Red Triage)
        $case1 = $db->fetchOne("SELECT id FROM `emergency_cases` WHERE `case_number` = 'ER-20261118-0001'");
        if (!$case1) {
            $db->query("INSERT INTO `emergency_cases` (`case_number`, `patient_id`, `temporary_patient_name`, `arrival_mode`, `arrival_time`, `triage_level`, `chief_complaint`, `attending_doctor_id`, `status`)
                        VALUES ('ER-20261118-0001', NULL, 'مجهول الهوية - حادث حركة بشارع النيل', 'ambulance', NOW() - INTERVAL 1 HOUR, 'red', 'إصابة رأس حادة وفقدان وعي جزئي إثر حادث مروري', :doc_id, 'under_treatment')", [
                'doc_id' => $docId,
            ]);
            $case1Id = $db->lastInsertId();

            // Triage assessment for Case 1
            $db->query("INSERT INTO `triage_assessments` (`emergency_case_id`, `triage_color`, `gcs_score`, `airway_status`, `breathing_status`, `circulation_status`, `systolic_bp`, `diastolic_bp`, `pulse_rate`, `respiratory_rate`, `temperature`, `oxygen_saturation`, `pain_score`, `triage_nurse_id`, `notes`, `assessed_at`)
                        VALUES (:cid, 'red', 10, 'threatened', 'dyspnea', 'weak', 90, 60, 122, 28, 36.8, 91, 8, :nurse_id, 'تأمين مجرى الهواء فوري، استدعاء جراح الطوارئ ومخ وأعصاب', NOW() - INTERVAL 50 MINUTE)", [
                'cid' => $case1Id,
                'nurse_id' => $adminId,
            ]);
        }

        // 2. Seed Emergency Case 2 (Yellow Triage - Patient 1)
        $case2 = $db->fetchOne("SELECT id FROM `emergency_cases` WHERE `case_number` = 'ER-20261118-0002'");
        if (!$case2) {
            $db->query("INSERT INTO `emergency_cases` (`case_number`, `patient_id`, `temporary_patient_name`, `arrival_mode`, `arrival_time`, `triage_level`, `chief_complaint`, `attending_doctor_id`, `status`)
                        VALUES ('ER-20261118-0002', :pid, NULL, 'walk_in', NOW() - INTERVAL 3 HOUR, 'yellow', 'ألم حاد بالجانب الأيمن للبطن مع حمى وغثيان', :doc_id, 'admitted')", [
                'pid' => $p1Id,
                'doc_id' => $docId,
            ]);
            $case2Id = $db->lastInsertId();

            $db->query("INSERT INTO `triage_assessments` (`emergency_case_id`, `triage_color`, `gcs_score`, `airway_status`, `breathing_status`, `circulation_status`, `systolic_bp`, `diastolic_bp`, `pulse_rate`, `respiratory_rate`, `temperature`, `oxygen_saturation`, `pain_score`, `triage_nurse_id`, `notes`, `assessed_at`)
                        VALUES (:cid, 'yellow', 15, 'clear', 'normal', 'stable', 125, 80, 88, 18, 38.4, 98, 7, :nurse_id, 'اشتباه التهاب زائدة دودية حاد، تم عمل موجات صوتية وقرار التنويم بالجراحة', NOW() - INTERVAL 2 HOUR)", [
                'cid' => $case2Id,
                'nurse_id' => $adminId,
            ]);
        } else {
            $case2Id = $case2['id'];
        }

        // 3. Seed Inpatient Admission (IPD)
        $adm = $db->fetchOne("SELECT id FROM `admissions` WHERE `admission_number` = 'ADM-2026-00001'");
        if (!$adm) {
            $db->query("INSERT INTO `admissions` (`admission_number`, `patient_id`, `admitting_doctor_id`, `department_id`, `emergency_case_id`, `admission_date`, `admission_type`, `admission_reason`, `provisional_diagnosis`, `status`)
                        VALUES ('ADM-2026-00001', :pid, :doc_id, :dept_id, :er_id, NOW() - INTERVAL 2 HOUR, 'emergency', 'تنويم عاجل لمتابعة جراحية ومحاليل وريدية', 'Acute Appendicitis - التهاب زائدة دودية حاد', 'admitted')", [
                'pid' => $p1Id,
                'doc_id' => $docId,
                'dept_id' => $intDeptId,
                'er_id' => $case2Id,
            ]);
            $admId = $db->lastInsertId();
        } else {
            $admId = $adm['id'];
        }

        // Ensure active bed assignment exists
        $hasBed = $db->fetchOne("SELECT id FROM `bed_assignments` WHERE `admission_id` = :aid AND `is_active` = 1", ['aid' => $admId]);
        if (!$hasBed) {
            $bed = $db->fetchOne("SELECT id FROM `beds` WHERE `status` = 'available' LIMIT 1");
            if ($bed) {
                $bedId = (int)$bed['id'];
                $bedModel = new \App\Models\BedAssignment();
                $bedModel->assignBed((int)$admId, $bedId, 'تسكين أولي بجناح الجراحة العامة');
            }
        }

        // Ensure Care Plan exists
        $hasPlan = $db->fetchOne("SELECT id FROM `nursing_care_plans` WHERE `admission_id` = :aid", ['aid' => $admId]);
        if (!$hasPlan) {
            $db->query("INSERT INTO `nursing_care_plans` (`admission_id`, `nursing_diagnosis`, `goals`, `interventions`, `evaluation`, `status`, `created_by`)
                        VALUES (:aid, 'ألم حاد مرتبط بالتهاب الزائدة الدودية والصفاق', 'تخفيف حدة الألم لمستوى أقل من 3/10 واستقرار العلامات الحيوية', 'إعطاء المسكنات الوريدية الموصوفة، وضع المريض بوضعية نصف جالس (Semi-Fowler)، ومراقبة النبض والضغط', 'استجاب المريض للمسكن وانخفضت شدة الألم إلى 4/10', 'active', :uid)", [
                'aid' => $admId,
                'uid' => $adminId,
            ]);
        }

        // Ensure Nursing Note exists
        $hasNote = $db->fetchOne("SELECT id FROM `nursing_notes` WHERE `admission_id` = :aid", ['aid' => $admId]);
        if (!$hasNote) {
            $db->query("INSERT INTO `nursing_notes` (`admission_id`, `nurse_id`, `note_type`, `note_content`, `recorded_at`)
                        VALUES (:aid, :nid, 'routine', 'تم استلام المريض بقسم التنويم وتركيب المحاليل الوريدية (Ringer Lactate)، المريض واعي ومستقر وعلاماته الحيوية ضمن المعدل الطبيعي.', NOW() - INTERVAL 1 HOUR)", [
                'aid' => $admId,
                'nid' => $adminId,
            ]);
        }

        // Ensure MAR exists
        $hasMar = $db->fetchOne("SELECT id FROM `medication_administrations` WHERE `admission_id` = :aid", ['aid' => $admId]);
        if (!$hasMar) {
            $marItems = [
                ['aid' => $admId, 'med' => 'Ceftriaxone 1g Vial', 'dose' => '1 جم وريدياً', 'route' => 'iv', 'st' => 'given', 'notes' => 'تم إجراء اختبار الحساسية أولاً وجاء سلبياً', 'nid' => $adminId, 'is_given' => true],
                ['aid' => $admId, 'med' => 'Paracetamol 1000mg IV Infusion', 'dose' => '100 مل وريدياً', 'route' => 'iv', 'st' => 'given', 'notes' => 'خافض حرارة ومسكن', 'nid' => $adminId, 'is_given' => true],
                ['aid' => $admId, 'med' => 'Metronidazole 500mg IV', 'dose' => '500 مجم بالتنقيط الوريدي', 'route' => 'iv', 'st' => 'pending', 'notes' => 'جرعة مسائية بعد 4 ساعات', 'nid' => null, 'is_given' => false],
            ];

            foreach ($marItems as $item) {
                if ($item['is_given']) {
                    $db->query("INSERT INTO `medication_administrations` (`admission_id`, `medicine_name`, `dosage`, `route`, `scheduled_time`, `administered_time`, `administered_by`, `status`, `notes`)
                                VALUES (:aid, :med, :dose, :route, NOW() - INTERVAL 1 HOUR, NOW() - INTERVAL 45 MINUTE, :nid, :st, :notes)", [
                        'aid' => $item['aid'],
                        'med' => $item['med'],
                        'dose' => $item['dose'],
                        'route' => $item['route'],
                        'nid' => $item['nid'],
                        'st' => $item['st'],
                        'notes' => $item['notes'],
                    ]);
                } else {
                    $db->query("INSERT INTO `medication_administrations` (`admission_id`, `medicine_name`, `dosage`, `route`, `scheduled_time`, `status`, `notes`)
                                VALUES (:aid, :med, :dose, :route, NOW() + INTERVAL 4 HOUR, :st, :notes)", [
                        'aid' => $item['aid'],
                        'med' => $item['med'],
                        'dose' => $item['dose'],
                        'route' => $item['route'],
                        'st' => $item['st'],
                        'notes' => $item['notes'],
                    ]);
                }
            }
        }
    }
}
