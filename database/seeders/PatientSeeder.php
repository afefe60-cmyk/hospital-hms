<?php

class PatientSeeder
{
    public function run(): void
    {
        $db = \App\Core\Database::getInstance();

        $admin = $db->fetchOne("SELECT id FROM `users` WHERE `username` = 'admin'");
        $adminId = $admin ? $admin['id'] : 1;

        $doc1 = $db->fetchOne("SELECT id FROM `doctors` LIMIT 1");
        $clinic1 = $db->fetchOne("SELECT id, department_id FROM `clinics` LIMIT 1");

        // 1. Patient 1: Mohamed Osman
        $p1 = $db->fetchOne("SELECT id FROM `patients` WHERE `mrn` = 'MRN-2026-000001'");
        if (!$p1) {
            $db->query("INSERT INTO `patients` (`mrn`, `national_id`, `first_name`, `second_name`, `third_name`, `last_name`, `gender`, `date_of_birth`, `blood_group`, `marital_status`, `phone_primary`, `address_state`, `address_city`, `emergency_contact_name`, `emergency_contact_phone`, `emergency_contact_relation`, `created_by`)
                        VALUES ('MRN-2026-000001', '110998877661', 'محمد', 'عثمان', 'عبد الله', 'الفكي', 'male', '1980-05-12', 'O+', 'married', '+249912111001', 'ولاية الخرطوم', 'بحري - الصافية', 'فاطمة عثمان (الأخت)', '+249912111002', 'أخت', :created_by)", [
                'created_by' => $adminId,
            ]);
            $p1Id = $db->lastInsertId();

            // Allergies for P1: Penicillin (severe)
            $db->query("INSERT INTO `patient_allergies` (`patient_id`, `allergen_type`, `allergen_name`, `severity`, `reaction_notes`, `recorded_by`)
                        VALUES (:pid, 'drug', 'Penicillin (بنسلين)', 'life_threatening', 'صدمة تحسسية حادة (Anaphylaxis) وضيق تنفس شديد', :rec_by)", [
                'pid' => $p1Id,
                'rec_by' => $adminId,
            ]);

            // Conditions for P1: Diabetes Mellitus Type 2
            $db->query("INSERT INTO `patient_conditions` (`patient_id`, `condition_name`, `icd10_code`, `diagnosed_date`, `is_active`)
                        VALUES (:pid, 'داء السكري من النوع الثاني (Type 2 Diabetes)', 'E11', '2018-09-20', 1)", [
                'pid' => $p1Id,
            ]);
        } else {
            $p1Id = $p1['id'];
        }

        // 2. Patient 2: Zeinab Ahmed
        $p2 = $db->fetchOne("SELECT id FROM `patients` WHERE `mrn` = 'MRN-2026-000002'");
        if (!$p2) {
            $db->query("INSERT INTO `patients` (`mrn`, `national_id`, `first_name`, `second_name`, `third_name`, `last_name`, `gender`, `date_of_birth`, `blood_group`, `marital_status`, `phone_primary`, `address_state`, `address_city`, `emergency_contact_name`, `emergency_contact_phone`, `emergency_contact_relation`, `created_by`)
                        VALUES ('MRN-2026-000002', '220998877662', 'زينب', 'أحمد', 'حسن', 'البشير', 'female', '1992-11-25', 'B+', 'married', '+249912222001', 'ولاية الجزيرة', 'ود مدني - حي الدرجة', 'أحمد حسن (الوالد)', '+249912222002', 'أب', :created_by)", [
                'created_by' => $adminId,
            ]);
            $p2Id = $db->lastInsertId();

            // Allergies for P2: Sulfa Drugs
            $db->query("INSERT INTO `patient_allergies` (`patient_id`, `allergen_type`, `allergen_name`, `severity`, `reaction_notes`, `recorded_by`)
                        VALUES (:pid, 'drug', 'Sulfa Drugs (مركبات السلفا)', 'moderate', 'طفح جلدي وحكة', :rec_by)", [
                'pid' => $p2Id,
                'rec_by' => $adminId,
            ]);

            // Conditions for P2: Hypertension
            $db->query("INSERT INTO `patient_conditions` (`patient_id`, `condition_name`, `icd10_code`, `diagnosed_date`, `is_active`)
                        VALUES (:pid, 'ارتفاع ضغط الدم الشرياني (Essential Hypertension)', 'I10', '2021-03-15', 1)", [
                'pid' => $p2Id,
            ]);
        } else {
            $p2Id = $p2['id'];
        }

        // 3. Seed Appointments and Visits for today
        if ($doc1 && $clinic1 && $p1Id && $p2Id) {
            $today = date('Y-m-d');

            // Appointment 1
            $app1 = $db->fetchOne("SELECT id FROM `appointments` WHERE `patient_id` = :pid AND `appointment_date` = :d", ['pid' => $p1Id, 'd' => $today]);
            if (!$app1) {
                $db->query("INSERT INTO `appointments` (`patient_id`, `doctor_id`, `clinic_id`, `appointment_date`, `appointment_time`, `token_number`, `status`, `created_by`)
                            VALUES (:pid, :doc, :clinic, :date, '09:00:00', 1, 'checked_in', :created_by)", [
                    'pid' => $p1Id,
                    'doc' => $doc1['id'],
                    'clinic' => $clinic1['id'],
                    'date' => $today,
                    'created_by' => $adminId,
                ]);

                // Visit for P1
                $vis1 = $db->fetchOne("SELECT id FROM `visits` WHERE `patient_id` = :pid AND DATE(`arrival_time`) = :d", ['pid' => $p1Id, 'd' => $today]);
                if (!$vis1) {
                    $visitNumber = 'VIS-' . date('Ymd') . '-00001';
                    $db->query("INSERT INTO `visits` (`visit_number`, `patient_id`, `visit_type`, `department_id`, `attending_doctor_id`, `arrival_time`, `status`, `created_by`)
                                VALUES (:vis_num, :pid, 'opd', :dep, :doc, NOW(), 'active', :created_by)", [
                        'vis_num' => $visitNumber,
                        'pid' => $p1Id,
                        'dep' => $clinic1['department_id'],
                        'doc' => $doc1['id'],
                        'created_by' => $adminId,
                    ]);
                    $vis1Id = $db->lastInsertId();

                    // Queue token for P1
                    $db->query("INSERT INTO `queues` (`clinic_id`, `visit_id`, `queue_number`, `status`) VALUES (:c, :v, 1, 'called')", [
                        'c' => $clinic1['id'],
                        'v' => $vis1Id,
                    ]);
                }
            }

            // Appointment 2
            $app2 = $db->fetchOne("SELECT id FROM `appointments` WHERE `patient_id` = :pid AND `appointment_date` = :d", ['pid' => $p2Id, 'd' => $today]);
            if (!$app2) {
                $db->query("INSERT INTO `appointments` (`patient_id`, `doctor_id`, `clinic_id`, `appointment_date`, `appointment_time`, `token_number`, `status`, `created_by`)
                            VALUES (:pid, :doc, :clinic, :date, '09:30:00', 2, 'booked', :created_by)", [
                    'pid' => $p2Id,
                    'doc' => $doc1['id'],
                    'clinic' => $clinic1['id'],
                    'date' => $today,
                    'created_by' => $adminId,
                ]);
            }
        }
    }
}
