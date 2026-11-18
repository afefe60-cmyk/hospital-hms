<?php

class HospitalStructureSeeder
{
    public function run(): void
    {
        $db = \App\Core\Database::getInstance();

        // 1. Seed Departments
        $departments = [
            ['code' => 'DEP-ER', 'name_ar' => 'قسم الحوادث والطوارئ', 'name_en' => 'Emergency Department', 'type' => 'medical'],
            ['code' => 'DEP-MED', 'name_ar' => 'قسم الباطنية العامة', 'name_en' => 'Internal Medicine Department', 'type' => 'medical'],
            ['code' => 'DEP-SURG', 'name_ar' => 'قسم الجراحة العامة', 'name_en' => 'General Surgery Department', 'type' => 'medical'],
            ['code' => 'DEP-PED', 'name_ar' => 'قسم طب الأطفال', 'name_en' => 'Pediatrics Department', 'type' => 'medical'],
            ['code' => 'DEP-OBS', 'name_ar' => 'قسم النساء والتوليد', 'name_en' => 'Obstetrics & Gynecology', 'type' => 'medical'],
            ['code' => 'DEP-LAB', 'name_ar' => 'قسم المختبرات الطبية', 'name_en' => 'Diagnostic Laboratory', 'type' => 'diagnostic'],
            ['code' => 'DEP-RAD', 'name_ar' => 'قسم الأشعة والتصوير الطبي', 'name_en' => 'Radiology & Medical Imaging', 'type' => 'diagnostic'],
            ['code' => 'DEP-PHAR', 'name_ar' => 'الصيدلية والإمداد الدوائي', 'name_en' => 'Pharmacy & Drug Supply', 'type' => 'pharmaceutical'],
            ['code' => 'DEP-FIN', 'name_ar' => 'الإدارة المالية والحسابات', 'name_en' => 'Finance & Accounting', 'type' => 'financial'],
            ['code' => 'DEP-ADMIN', 'name_ar' => 'الشؤون الإدارية والموارد البشرية', 'name_en' => 'Human Resources & Administration', 'type' => 'administrative'],
        ];

        foreach ($departments as $d) {
            $existing = $db->fetchOne("SELECT id FROM `departments` WHERE `code` = :code", ['code' => $d['code']]);
            if (!$existing) {
                $db->query("INSERT INTO `departments` (`code`, `name_ar`, `name_en`, `type`) VALUES (:code, :name_ar, :name_en, :type)", $d);
            }
        }

        // 2. Seed Specialties
        $specialties = [
            ['code' => 'SPEC-INT', 'name_ar' => 'الأمراض الباطنية', 'name_en' => 'Internal Medicine'],
            ['code' => 'SPEC-SURG', 'name_ar' => 'الجراحة العامة', 'name_en' => 'General Surgery'],
            ['code' => 'SPEC-PED', 'name_ar' => 'طب الأطفال وحديثي الولادة', 'name_en' => 'Pediatrics & Neonatology'],
            ['code' => 'SPEC-OBS', 'name_ar' => 'النساء والتوليد', 'name_en' => 'Obstetrics & Gynecology'],
            ['code' => 'SPEC-ORTH', 'name_ar' => 'جراحة العظام والمفاصل', 'name_en' => 'Orthopedic Surgery'],
            ['code' => 'SPEC-ANES', 'name_ar' => 'التخدير والعناية المكثفة', 'name_en' => 'Anesthesia & Intensive Care'],
            ['code' => 'SPEC-ER', 'name_ar' => 'طب الطوارئ والإصابات', 'name_en' => 'Emergency & Trauma Medicine'],
        ];

        foreach ($specialties as $s) {
            $existing = $db->fetchOne("SELECT id FROM `specialties` WHERE `code` = :code", ['code' => $s['code']]);
            if (!$existing) {
                $db->query("INSERT INTO `specialties` (`code`, `name_ar`, `name_en`) VALUES (:code, :name_ar, :name_en)", $s);
            }
        }

        // 3. Seed Shifts
        $shifts = [
            ['name_ar' => 'الوردية الصباحية', 'name_en' => 'Morning Shift', 'start_time' => '08:00:00', 'end_time' => '16:00:00'],
            ['name_ar' => 'الوردية المسائية', 'name_en' => 'Evening Shift', 'start_time' => '16:00:00', 'end_time' => '00:00:00'],
            ['name_ar' => 'الوردية الليلية', 'name_en' => 'Night Shift', 'start_time' => '00:00:00', 'end_time' => '08:00:00'],
        ];

        foreach ($shifts as $sh) {
            $existing = $db->fetchOne("SELECT id FROM `shifts` WHERE `name_en` = :name_en", ['name_en' => $sh['name_en']]);
            if (!$existing) {
                $db->query("INSERT INTO `shifts` (`name_ar`, `name_en`, `start_time`, `end_time`) VALUES (:name_ar, :name_en, :start_time, :end_time)", $sh);
            }
        }

        // 4. Seed Clinics
        $medDep = $db->fetchOne("SELECT id FROM `departments` WHERE `code` = 'DEP-MED'");
        $surgDep = $db->fetchOne("SELECT id FROM `departments` WHERE `code` = 'DEP-SURG'");
        $pedDep = $db->fetchOne("SELECT id FROM `departments` WHERE `code` = 'DEP-PED'");

        if ($medDep && $surgDep && $pedDep) {
            $clinics = [
                ['department_id' => $medDep['id'], 'room_number' => '101', 'name_ar' => 'عيادة الباطنية 1', 'name_en' => 'Internal Medicine Clinic 1'],
                ['department_id' => $medDep['id'], 'room_number' => '102', 'name_ar' => 'عيادة السكري والغدد الصماء', 'name_en' => 'Diabetes & Endocrine Clinic'],
                ['department_id' => $surgDep['id'], 'room_number' => '201', 'name_ar' => 'عيادة الجراحة العامة', 'name_en' => 'General Surgery Clinic'],
                ['department_id' => $pedDep['id'], 'room_number' => '301', 'name_ar' => 'عيادة الأطفال العامة', 'name_en' => 'Pediatrics Clinic'],
            ];

            foreach ($clinics as $c) {
                $existing = $db->fetchOne("SELECT id FROM `clinics` WHERE `room_number` = :room_number AND `department_id` = :department_id", [
                    'room_number' => $c['room_number'],
                    'department_id' => $c['department_id'],
                ]);
                if (!$existing) {
                    $db->query("INSERT INTO `clinics` (`department_id`, `room_number`, `name_ar`, `name_en`) VALUES (:department_id, :room_number, :name_ar, :name_en)", $c);
                }
            }
        }

        // 5. Seed Inpatient Structure: Wards, Rooms, and Beds
        if ($surgDep && $medDep) {
            // Ward 1: Male Surgery Ward
            $ward1 = $db->fetchOne("SELECT id FROM `wards` WHERE `name_en` = 'Male Surgery Ward'");
            if (!$ward1) {
                $db->query("INSERT INTO `wards` (`department_id`, `name_ar`, `name_en`, `gender_type`, `floor_number`) VALUES
                            (:department_id, 'عنبر الجراحة رجال', 'Male Surgery Ward', 'male', 'الطابق 1')", [
                    'department_id' => $surgDep['id'],
                ]);
                $ward1Id = $db->lastInsertId();
            } else {
                $ward1Id = $ward1['id'];
            }

            // Ward 2: Female Medicine Ward
            $ward2 = $db->fetchOne("SELECT id FROM `wards` WHERE `name_en` = 'Female Medicine Ward'");
            if (!$ward2) {
                $db->query("INSERT INTO `wards` (`department_id`, `name_ar`, `name_en`, `gender_type`, `floor_number`) VALUES
                            (:department_id, 'عنبر الباطنية نساء', 'Female Medicine Ward', 'female', 'الطابق 2')", [
                    'department_id' => $medDep['id'],
                ]);
                $ward2Id = $db->lastInsertId();
            } else {
                $ward2Id = $ward2['id'];
            }

            // Rooms in Ward 1
            $room101 = $db->fetchOne("SELECT id FROM `rooms` WHERE `ward_id` = :ward_id AND `room_number` = '101'", ['ward_id' => $ward1Id]);
            if (!$room101) {
                $db->query("INSERT INTO `rooms` (`ward_id`, `room_number`, `room_type`, `daily_rate`) VALUES (:ward_id, '101', 'standard', 35000.00)", [
                    'ward_id' => $ward1Id,
                ]);
                $room101Id = $db->lastInsertId();

                // Beds in Room 101
                $db->query("INSERT INTO `beds` (`room_id`, `bed_number`, `status`) VALUES
                            (:r1, 'BED-101-A', 'available'),
                            (:r2, 'BED-101-B', 'occupied'),
                            (:r3, 'BED-101-C', 'cleaning')", [
                    'r1' => $room101Id, 'r2' => $room101Id, 'r3' => $room101Id
                ]);
            }

            $room102 = $db->fetchOne("SELECT id FROM `rooms` WHERE `ward_id` = :ward_id AND `room_number` = '102-VIP'", ['ward_id' => $ward1Id]);
            if (!$room102) {
                $db->query("INSERT INTO `rooms` (`ward_id`, `room_number`, `room_type`, `daily_rate`) VALUES (:ward_id, '102-VIP', 'vip', 80000.00)", [
                    'ward_id' => $ward1Id,
                ]);
                $room102Id = $db->lastInsertId();

                $db->query("INSERT INTO `beds` (`room_id`, `bed_number`, `status`) VALUES (:r, 'BED-VIP-1', 'available')", ['r' => $room102Id]);
            }

            // Rooms in Ward 2
            $room201 = $db->fetchOne("SELECT id FROM `rooms` WHERE `ward_id` = :ward_id AND `room_number` = '201'", ['ward_id' => $ward2Id]);
            if (!$room201) {
                $db->query("INSERT INTO `rooms` (`ward_id`, `room_number`, `room_type`, `daily_rate`) VALUES (:ward_id, '201', 'standard', 35000.00)", [
                    'ward_id' => $ward2Id,
                ]);
                $room201Id = $db->lastInsertId();

                $db->query("INSERT INTO `beds` (`room_id`, `bed_number`, `status`) VALUES
                            (:r1, 'BED-201-A', 'available'),
                            (:r2, 'BED-201-B', 'available')", [
                    'r1' => $room201Id, 'r2' => $room201Id
                ]);
            }
        }

        // 6. Seed Operating Rooms
        $operatingRooms = [
            ['name_ar' => 'غرفة العمليات الكبرى 1', 'name_en' => 'Major Operating Theater 1', 'status' => 'available'],
            ['name_ar' => 'غرفة العمليات الصغرى والطوارئ', 'name_en' => 'Minor & ER Surgery Suite', 'status' => 'available'],
        ];

        foreach ($operatingRooms as $or) {
            $existing = $db->fetchOne("SELECT id FROM `operating_rooms` WHERE `name_en` = :name_en", ['name_en' => $or['name_en']]);
            if (!$existing) {
                $db->query("INSERT INTO `operating_rooms` (`name_ar`, `name_en`, `status`) VALUES (:name_ar, :name_en, :status)", $or);
            }
        }

        // 7. Seed Staff Doctors & Nurses
        if ($medDep && $surgDep) {
            $specInt = $db->fetchOne("SELECT id FROM `specialties` WHERE `code` = 'SPEC-INT'");
            $specSurg = $db->fetchOne("SELECT id FROM `specialties` WHERE `code` = 'SPEC-SURG'");

            // Doctor 1: Dr. Tariq (Internal Medicine)
            $emp1 = $db->fetchOne("SELECT id FROM `employees` WHERE `employee_code` = 'EMP-DOC-001'");
            if (!$emp1) {
                $db->query("INSERT INTO `employees` (`employee_code`, `first_name`, `second_name`, `last_name`, `gender`, `national_id`, `phone`, `email`, `department_id`, `job_title`, `hire_date`, `basic_salary`)
                            VALUES ('EMP-DOC-001', 'طارق', 'عثمان', 'إبراهيم', 'male', '110987654321', '+249912000001', 'tariq.ibrahim@hospital.sd', :dep, 'استشاري أمراض باطنية', '2022-01-15', 850000.00)", [
                    'dep' => $medDep['id'],
                ]);
                $emp1Id = $db->lastInsertId();

                if ($specInt) {
                    $db->query("INSERT INTO `doctors` (`employee_id`, `specialty_id`, `medical_license_number`, `degree_title`, `consultation_fee`, `commission_percentage`)
                                VALUES (:emp_id, :spec_id, 'SMC-MD-2015-8842', 'استشاري أول أمراض باطنية', 25000.00, 30.00)", [
                        'emp_id' => $emp1Id,
                        'spec_id' => $specInt['id'],
                    ]);
                }
            }

            // Doctor 2: Dr. Sara (General Surgery)
            $emp2 = $db->fetchOne("SELECT id FROM `employees` WHERE `employee_code` = 'EMP-DOC-002'");
            if (!$emp2) {
                $db->query("INSERT INTO `employees` (`employee_code`, `first_name`, `second_name`, `last_name`, `gender`, `national_id`, `phone`, `email`, `department_id`, `job_title`, `hire_date`, `basic_salary`)
                            VALUES ('EMP-DOC-002', 'سارة', 'عبد الرحيم', 'محمد', 'female', '220987654322', '+249912000002', 'sara.abdelrahim@hospital.sd', :dep, 'أخصائية جراحة عامة', '2023-04-01', 750000.00)", [
                    'dep' => $surgDep['id'],
                ]);
                $emp2Id = $db->lastInsertId();

                if ($specSurg) {
                    $db->query("INSERT INTO `doctors` (`employee_id`, `specialty_id`, `medical_license_number`, `degree_title`, `consultation_fee`, `commission_percentage`)
                                VALUES (:emp_id, :spec_id, 'SMC-MD-2018-9921', 'أخصائية جراحة عامة ومناظير', 30000.00, 35.00)", [
                        'emp_id' => $emp2Id,
                        'spec_id' => $specSurg['id'],
                    ]);
                }
            }
        }
    }
}
