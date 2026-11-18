<?php

use App\Core\Database;

class SurgeryAndIcuSeeder
{
    public function run(): void
    {
        $db = Database::getInstance();

        // 1. Ensure Anesthesiologist & Intensivist Doctor exists
        $anesDoctor = $db->fetchOne("SELECT d.id FROM `doctors` d
                                     INNER JOIN `specialties` s ON d.specialty_id = s.id
                                     WHERE s.code = 'SPEC-ANES'");

        if (!$anesDoctor) {
            $spec = $db->fetchOne("SELECT id FROM `specialties` WHERE `code` = 'SPEC-ANES'");
            $dep = $db->fetchOne("SELECT id FROM `departments` WHERE `code` = 'DEP-SURG'");
            if (!$dep) {
                $dep = $db->fetchOne("SELECT id FROM `departments` LIMIT 1");
            }

            if ($spec && $dep) {
                $emp = $db->fetchOne("SELECT id FROM `employees` WHERE `employee_code` = 'EMP-DOC-003'");
                if (!$emp) {
                    $db->query("INSERT INTO `employees` (`employee_code`, `first_name`, `second_name`, `last_name`, `gender`, `national_id`, `phone`, `email`, `department_id`, `job_title`, `hire_date`, `basic_salary`)
                                VALUES ('EMP-DOC-003', 'أحمد', 'البشير', 'عوض', 'male', '110987654333', '+249912000003', 'ahmed.elbashir@hospital.sd', :dep, 'استشاري التخدير والعناية المكثفة', '2023-01-10', 820000.00)", [
                        'dep' => $dep['id']
                    ]);
                    $empId = $db->lastInsertId();
                } else {
                    $empId = $emp['id'];
                }

                $db->query("INSERT INTO `doctors` (`employee_id`, `specialty_id`, `medical_license_number`, `degree_title`, `consultation_fee`, `commission_percentage`)
                            VALUES (:emp, :spec, 'SMC-MD-2016-5541', 'استشاري التخدير وعناية الحالات الحرجة', 25000.00, 30.00)", [
                    'emp' => $empId,
                    'spec' => $spec['id'],
                ]);
                $anesDoctorId = (int)$db->lastInsertId();
            } else {
                $firstDoc = $db->fetchOne("SELECT id FROM `doctors` LIMIT 1");
                $anesDoctorId = $firstDoc ? (int)$firstDoc['id'] : 1;
            }
        } else {
            $anesDoctorId = (int)$anesDoctor['id'];
        }

        // Get Surgeon doctor
        $surgeonDoc = $db->fetchOne("SELECT d.id FROM `doctors` d
                                     INNER JOIN `specialties` s ON d.specialty_id = s.id
                                     WHERE s.code = 'SPEC-SURG'");
        $leadSurgeonId = $surgeonDoc ? (int)$surgeonDoc['id'] : $anesDoctorId;

        // 2. Ensure ICU Ward, Room, and Beds exist
        $icuWard = $db->fetchOne("SELECT id FROM `wards` WHERE `name_en` LIKE '%ICU%' OR `name_ar` LIKE '%العناية%'");
        if (!$icuWard) {
            $dep = $db->fetchOne("SELECT id FROM `departments` WHERE `code` = 'DEP-SURG'");
            if (!$dep) $dep = $db->fetchOne("SELECT id FROM `departments` LIMIT 1");

            $db->query("INSERT INTO `wards` (`department_id`, `name_ar`, `name_en`, `gender_type`, `floor_number`)
                        VALUES (:dep, 'وحدة العناية المركزة (ICU)', 'Intensive Care Unit (ICU)', 'unisex', 'الطابق 2')", [
                'dep' => $dep['id']
            ]);
            $icuWardId = (int)$db->lastInsertId();

            $db->query("INSERT INTO `rooms` (`ward_id`, `room_number`, `room_type`, `daily_rate`)
                        VALUES (:wid, 'ICU-101', 'isolation', 75000.00)", [
                'wid' => $icuWardId
            ]);
            $icuRoomId = (int)$db->lastInsertId();

            $db->query("INSERT INTO `beds` (`room_id`, `bed_number`, `status`) VALUES
                        (:r1, 'ICU-BED-01', 'occupied'),
                        (:r2, 'ICU-BED-02', 'available'),
                        (:r3, 'ICU-BED-03', 'available')", [
                'r1' => $icuRoomId,
                'r2' => $icuRoomId,
                'r3' => $icuRoomId
            ]);
        }

        // Find ICU Bed 1
        $icuBed = $db->fetchOne("SELECT b.id FROM `beds` b
                                 INNER JOIN `rooms` r ON b.room_id = r.id
                                 WHERE r.room_type = 'icu' OR b.bed_number LIKE 'ICU%' LIMIT 1");
        $icuBedId = $icuBed ? (int)$icuBed['id'] : 1;

        // Operating Rooms
        $or1 = $db->fetchOne("SELECT id FROM `operating_rooms` WHERE `name_en` LIKE '%Major%' LIMIT 1");
        $or1Id = $or1 ? (int)$or1['id'] : 1;

        $or2 = $db->fetchOne("SELECT id FROM `operating_rooms` WHERE `name_en` LIKE '%Minor%' LIMIT 1");
        $or2Id = $or2 ? (int)$or2['id'] : $or1Id;

        // Patients & Admissions
        $patient = $db->fetchOne("SELECT id FROM `patients` ORDER BY id ASC LIMIT 1");
        $patientId = $patient ? (int)$patient['id'] : 1;

        $admission = $db->fetchOne("SELECT id FROM `admissions` WHERE `patient_id` = :pid LIMIT 1", ['pid' => $patientId]);
        $admissionId = $admission ? (int)$admission['id'] : null;

        $nurse = $db->fetchOne("SELECT id FROM `employees` WHERE `job_title` LIKE '%تمريض%' OR `job_title` LIKE '%نيرس%' LIMIT 1");
        $nurseId = $nurse ? (int)$nurse['id'] : null;

        $adminUser = $db->fetchOne("SELECT id FROM `users` WHERE `username` = 'admin'");
        $adminUserId = $adminUser ? (int)$adminUser['id'] : 1;

        // 3. Seed Surgeries
        $existingSur1 = $db->fetchOne("SELECT id FROM `surgeries` WHERE `surgery_number` = 'SUR-20261118-0001'");
        if (!$existingSur1) {
            $db->query("INSERT INTO `surgeries` (
                `surgery_number`, `patient_id`, `admission_id`, `operating_room_id`,
                `lead_surgeon_id`, `assistant_surgeon_id`, `anesthesiologist_id`, `scrub_nurse_id`,
                `surgery_type`, `procedure_name`, `scheduled_start`, `scheduled_end`,
                `actual_start`, `actual_end`, `status`, `pre_op_diagnosis`, `post_op_diagnosis`,
                `surgery_notes`, `created_by`
            ) VALUES (
                'SUR-20261118-0001', :patient_id, :admission_id, :or_id,
                :lead_surg, NULL, :anes_id, :nurse_id,
                'elective', 'استئصال المرارة بالمنظار (Laparoscopic Cholecystectomy)',
                '2026-11-18 09:00:00', '2026-11-18 11:30:00',
                '2026-11-18 09:15:00', '2026-11-18 11:15:00',
                'completed',
                'حصوات مرارية مزمنة مصحوبة بنوبات ألم متكررة (Chronic Cholecystitis with Cholelithiasis)',
                'تم استئصال المرارة بنجاح دون مضاعفات جراحية مع عزل القناة المرارية بدقة',
                'تم إدخال المنظار بنجاح عن طريق 4 منافذ (Trocar Ports). تشريح عنق المرارة والقناة المرارية وشريان المرارة وتدبيسها بأمان. تم استخراج كيس المرارة المحتوي على عدة حصوات. لا يوجد نزيف نشط. تم إغلاق الجروح بغرز تحت الجلد.',
                :created_by
            )", [
                'patient_id' => $patientId,
                'admission_id' => $admissionId,
                'or_id' => $or1Id,
                'lead_surg' => $leadSurgeonId,
                'anes_id' => $anesDoctorId,
                'nurse_id' => $nurseId,
                'created_by' => $adminUserId,
            ]);
            $sur1Id = (int)$db->lastInsertId();

            // Seed Anesthesia Record for Surgery 1
            $db->query("INSERT INTO `anesthesia_records` (
                `surgery_id`, `anesthesiologist_id`, `anesthesia_type`, `asa_physical_status`,
                `pre_anesthesia_assessment`, `airway_assessment`, `start_time`, `end_time`,
                `agents_used`, `recovery_score`, `complications`, `anesthesia_notes`
            ) VALUES (
                :sid, :anes_id, 'general', 'II',
                'المريض لائق للتخدير العام تحت تصنيف ASA II لوجود تاريخ ارتفاع ضغط الدم مضبوط بالأدوية. الفحوصات مقبولة.',
                'Mallampati Class I - سهولة التنبيب الرغامي (Intubation)',
                '2026-11-18 08:50:00', '2026-11-18 11:25:00',
                'Propofol 150mg, Fentanyl 100mcg, Rocuronium 50mg, Isoflurane 1.5% in O2/Air mixture',
                'Aldrete Score: 10/10',
                'لا توجد مضاعفات تخديرية أثناء الجراحة أو بالإفاقة (PACU)',
                'تم التخدير بسلاسة واستقرار كامل للمؤشرات الحيوية وضغط الدم خلال كامل فترة العملية. استيقاظ المريض واعي وموجه بالكامل.'
            )", [
                'sid' => $sur1Id,
                'anes_id' => $anesDoctorId
            ]);
        }

        // Surgery 2: Scheduled / Today
        $existingSur2 = $db->fetchOne("SELECT id FROM `surgeries` WHERE `surgery_number` = 'SUR-20261118-0002'");
        if (!$existingSur2) {
            $db->query("INSERT INTO `surgeries` (
                `surgery_number`, `patient_id`, `admission_id`, `operating_room_id`,
                `lead_surgeon_id`, `assistant_surgeon_id`, `anesthesiologist_id`, `scrub_nurse_id`,
                `surgery_type`, `procedure_name`, `scheduled_start`, `scheduled_end`,
                `actual_start`, `actual_end`, `status`, `pre_op_diagnosis`, `post_op_diagnosis`,
                `surgery_notes`, `created_by`
            ) VALUES (
                'SUR-20261118-0002', :patient_id, :admission_id, :or_id,
                :lead_surg, NULL, :anes_id, :nurse_id,
                'emergency', 'استكشاف طارئ واستئصال الزائدة الدودية (Emergency Appendectomy)',
                '2026-11-18 14:00:00', '2026-11-18 15:30:00',
                '2026-11-18 14:10:00', NULL,
                'in_progress',
                'التهاب حاد في الزائدة الدودية مع علامات تهيج بريتوني (Acute Suppurative Appendicitis)',
                NULL,
                'الجراحة جارية الآن في المسار المعقم.',
                :created_by
            )", [
                'patient_id' => $patientId,
                'admission_id' => $admissionId,
                'or_id' => $or2Id,
                'lead_surg' => $leadSurgeonId,
                'anes_id' => $anesDoctorId,
                'nurse_id' => $nurseId,
                'created_by' => $adminUserId,
            ]);
        }

        // 4. Seed ICU Stay & Hourly Observations
        $existingIcu = $db->fetchOne("SELECT id FROM `icu_stays` WHERE `stay_number` = 'ICU-20261118-0001'");
        if (!$existingIcu) {
            $db->query("INSERT INTO `icu_stays` (
                `stay_number`, `patient_id`, `admission_id`, `bed_id`, `attending_intensivist_id`,
                `admitted_at`, `admission_reason`, `apache_score`, `ventilator_status`, `status`
            ) VALUES (
                'ICU-20261118-0001', :patient_id, :admission_id, :bed_id, :doctor_id,
                '2026-11-18 06:00:00',
                'صدمة إنتانية حادة وفشل تنفسي حاد يستدعي مراقبة ديناميكية الدم والتنفس الاصطناعي (Septic Shock & ARDS)',
                18, 'invasive', 'admitted'
            )", [
                'patient_id' => $patientId,
                'admission_id' => $admissionId,
                'bed_id' => $icuBedId,
                'doctor_id' => $anesDoctorId
            ]);
            $icuStayId = (int)$db->lastInsertId();

            // Insert 3 hourly observations for this ICU stay
            $observations = [
                [
                    'recorded_at' => '2026-11-18 07:00:00',
                    'systolic_bp' => 90,
                    'diastolic_bp' => 55,
                    'pulse_rate' => 118,
                    'respiratory_rate' => 24,
                    'temperature' => 38.6,
                    'oxygen_saturation' => 92.0,
                    'gcs_score' => 10,
                    'pupil_reaction' => 'brisk',
                    'ventilator_mode' => 'SIMV+PS',
                    'fio2' => 50,
                    'peep' => 8,
                    'urine_output_ml' => 40,
                    'iv_fluids_ml' => 500,
                    'arterial_blood_gas' => 'pH: 7.28, PaCO2: 48, PaO2: 78, HCO3: 19, Lactate: 3.2',
                    'clinical_notes' => 'المريض تحت التهدئة، استجابة مبدئية للمحاليل ورافعات الضغط (Noradrenaline 0.1 mcg/kg/min).'
                ],
                [
                    'recorded_at' => '2026-11-18 09:00:00',
                    'systolic_bp' => 105,
                    'diastolic_bp' => 65,
                    'pulse_rate' => 98,
                    'respiratory_rate' => 20,
                    'temperature' => 38.1,
                    'oxygen_saturation' => 96.0,
                    'gcs_score' => 11,
                    'pupil_reaction' => 'brisk',
                    'ventilator_mode' => 'SIMV+PS',
                    'fio2' => 45,
                    'peep' => 6,
                    'urine_output_ml' => 65,
                    'iv_fluids_ml' => 350,
                    'arterial_blood_gas' => 'pH: 7.34, PaCO2: 42, PaO2: 90, HCO3: 21, Lactate: 2.4',
                    'clinical_notes' => 'تحسن في ضغط الدم الترويضي (MAP > 65)، تحسن في إدرار البول وانخفاض نسبة اللاكتات.'
                ],
                [
                    'recorded_at' => '2026-11-18 11:00:00',
                    'systolic_bp' => 115,
                    'diastolic_bp' => 72,
                    'pulse_rate' => 86,
                    'respiratory_rate' => 18,
                    'temperature' => 37.5,
                    'oxygen_saturation' => 98.0,
                    'gcs_score' => 12,
                    'pupil_reaction' => 'brisk',
                    'ventilator_mode' => 'CPAP/PSV',
                    'fio2' => 40,
                    'peep' => 5,
                    'urine_output_ml' => 80,
                    'iv_fluids_ml' => 250,
                    'arterial_blood_gas' => 'pH: 7.39, PaCO2: 38, PaO2: 98, HCO3: 23, Lactate: 1.6',
                    'clinical_notes' => 'المريض مستقر، بدء تقليل دعم التنفس الصناعي (Weaning Trial) وتقليل النورأدرينالين تدريجياً.'
                ],
            ];

            foreach ($observations as $obs) {
                $db->query("INSERT INTO `icu_observations` (
                    `icu_stay_id`, `recorded_at`, `recorded_by`, `systolic_bp`, `diastolic_bp`,
                    `pulse_rate`, `respiratory_rate`, `temperature`, `oxygen_saturation`,
                    `gcs_score`, `pupil_reaction`, `ventilator_mode`, `fio2`, `peep`,
                    `urine_output_ml`, `iv_fluids_ml`, `arterial_blood_gas`, `clinical_notes`
                ) VALUES (
                    :icu_stay_id, :recorded_at, :recorded_by, :systolic_bp, :diastolic_bp,
                    :pulse_rate, :respiratory_rate, :temperature, :oxygen_saturation,
                    :gcs_score, :pupil_reaction, :ventilator_mode, :fio2, :peep,
                    :urine_output_ml, :iv_fluids_ml, :arterial_blood_gas, :clinical_notes
                )", array_merge($obs, [
                    'icu_stay_id' => $icuStayId,
                    'recorded_by' => $adminUserId
                ]));
            }
        }
    }
}
