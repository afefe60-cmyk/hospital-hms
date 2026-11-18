<?php

use App\Core\Database;

class HrAndPayrollSeeder
{
    public function run(): void
    {
        $db = Database::getInstance();

        // 1. Seed additional staff employees
        $additionalEmployees = [
            [
                'employee_code' => 'EMP-NUR-001',
                'first_name' => 'مروة',
                'second_name' => 'عبد الله',
                'last_name' => 'عثمان',
                'gender' => 'female',
                'national_id' => 'SDN-1994-0988231',
                'phone' => '+249912001122',
                'email' => 'marwa.nurse@hospital.sd',
                'department_id' => 3, // Surgery & Inpatient
                'job_title' => 'مشرفة التمريض العام والعناية',
                'hire_date' => '2023-01-15',
                'basic_salary' => 420000.00,
                'status' => 'active',
            ],
            [
                'employee_code' => 'EMP-PHA-001',
                'first_name' => 'عثمان',
                'second_name' => 'الفاتح',
                'last_name' => 'النور',
                'gender' => 'male',
                'national_id' => 'SDN-1991-0811234',
                'phone' => '+249912334455',
                'email' => 'osman.pharma@hospital.sd',
                'department_id' => 8, // Pharmacy
                'job_title' => 'صيدلي أول ومسؤول الإمداد الدوائي',
                'hire_date' => '2023-03-01',
                'basic_salary' => 480000.00,
                'status' => 'active',
            ],
            [
                'employee_code' => 'EMP-LAB-001',
                'first_name' => 'رانيا',
                'second_name' => 'الفاضل',
                'last_name' => 'صديق',
                'gender' => 'female',
                'national_id' => 'SDN-1995-0455678',
                'phone' => '+249912556677',
                'email' => 'rania.lab@hospital.sd',
                'department_id' => 6, // Lab
                'job_title' => 'أخصائية مختبرات طبية وتحاليل سريرية',
                'hire_date' => '2023-05-10',
                'basic_salary' => 450000.00,
                'status' => 'active',
            ],
            [
                'employee_code' => 'EMP-ACC-001',
                'first_name' => 'إبراهيم',
                'second_name' => 'مصطفى',
                'last_name' => 'حسن',
                'gender' => 'male',
                'national_id' => 'SDN-1990-0322119',
                'phone' => '+249912778899',
                'email' => 'ibrahim.acc@hospital.sd',
                'department_id' => 9, // Finance
                'job_title' => 'محاسب مالي ورئيس الخزينة',
                'hire_date' => '2022-11-01',
                'basic_salary' => 400000.00,
                'status' => 'active',
            ],
        ];

        foreach ($additionalEmployees as $emp) {
            $exists = $db->fetchOne("SELECT id FROM `employees` WHERE `employee_code` = :code", ['code' => $emp['employee_code']]);
            if (!$exists) {
                $db->query(
                    "INSERT INTO `employees` (`employee_code`, `first_name`, `second_name`, `last_name`, `gender`, `national_id`, `phone`, `email`, `department_id`, `job_title`, `hire_date`, `basic_salary`, `status`)
                     VALUES (:employee_code, :first_name, :second_name, :last_name, :gender, :national_id, :phone, :email, :department_id, :job_title, :hire_date, :basic_salary, :status)",
                    $emp
                );
            }
        }

        // Get admin user ID
        $admin = $db->fetchOne("SELECT id FROM `users` WHERE `username` = 'admin' LIMIT 1");
        $adminId = $admin ? (int)$admin['id'] : 1;

        // 2. Seed Weekly Rosters for current week
        $today = date('Y-m-d');
        $allEmps = $db->fetchAll("SELECT id, department_id, job_title FROM `employees`");

        $rosterAssignments = [
            // Today's roster
            ['emp_id' => 1, 'shift' => 1, 'role' => 'طبيب استشاري - عيادة الباطنية الصباحية', 'date' => $today],
            ['emp_id' => 2, 'shift' => 1, 'role' => 'طبيبة جراحة - عمليات مجدولة', 'date' => $today],
            ['emp_id' => 3, 'shift' => 2, 'role' => 'استشاري تخدير مناوب - المساء', 'date' => $today],
        ];

        // Also add other days of current week
        for ($i = -2; $i <= 4; $i++) {
            $dutyDate = date('Y-m-d', strtotime("{$today} {$i} days"));
            foreach ($allEmps as $idx => $e) {
                $shiftId = (($idx + abs($i)) % 3) + 1;
                $chk = $db->fetchOne("SELECT id FROM `employee_rosters` WHERE `employee_id` = :emp_id AND `duty_date` = :duty_date", [
                    'emp_id' => $e['id'],
                    'duty_date' => $dutyDate,
                ]);

                if (!$chk) {
                    $db->query(
                        "INSERT INTO `employee_rosters` (`employee_id`, `shift_id`, `department_id`, `duty_date`, `duty_role`, `status`, `notes`)
                         VALUES (:emp_id, :shift_id, :dept_id, :duty_date, :duty_role, 'scheduled', 'مناوبة دورية معتمدة')",
                        [
                            'emp_id' => $e['id'],
                            'shift_id' => $shiftId,
                            'dept_id' => $e['department_id'],
                            'duty_date' => $dutyDate,
                            'duty_role' => $e['job_title'] . ' - تغطية الوردية',
                        ]
                    );
                }
            }
        }

        // 3. Seed Employee Leaves
        $leaves = [
            [
                'employee_id' => 2, // Dr. Sara
                'leave_type' => 'annual',
                'start_date' => date('Y-m-d', strtotime('+7 days')),
                'end_date' => date('Y-m-d', strtotime('+12 days')),
                'total_days' => 6,
                'reason' => 'إجازة سنوية اعتيادية معتمدة',
                'status' => 'approved',
                'approved_by' => $adminId,
            ],
            [
                'employee_id' => 1, // Dr. Tariq
                'leave_type' => 'emergency',
                'start_date' => date('Y-m-d', strtotime('+15 days')),
                'end_date' => date('Y-m-d', strtotime('+16 days')),
                'total_days' => 2,
                'reason' => 'ظرف عائلي طارئ',
                'status' => 'pending',
                'approved_by' => null,
            ],
        ];

        foreach ($leaves as $l) {
            $chk = $db->fetchOne("SELECT id FROM `employee_leaves` WHERE `employee_id` = :emp_id AND `start_date` = :s_date", [
                'emp_id' => $l['employee_id'],
                's_date' => $l['start_date'],
            ]);
            if (!$chk) {
                $db->query(
                    "INSERT INTO `employee_leaves` (`employee_id`, `leave_type`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `approved_by`)
                     VALUES (:employee_id, :leave_type, :start_date, :end_date, :total_days, :reason, :status, :approved_by)",
                    $l
                );
            }
        }

        // 4. Seed Payroll for November 2026 (Month 11, Year 2026)
        $month = 11;
        $year = 2026;
        $activeEmployees = $db->fetchAll("SELECT * FROM `employees` WHERE `status` = 'active'");

        $counter = 1;
        foreach ($activeEmployees as $emp) {
            $empId = (int)$emp['id'];
            $chk = $db->fetchOne("SELECT id FROM `payroll` WHERE `employee_id` = :emp_id AND `salary_month` = :m AND `salary_year` = :y", [
                'emp_id' => $empId,
                'm' => $month,
                'y' => $year,
            ]);

            if (!$chk) {
                $payNum = sprintf("PAY-%04d%02d-%05d", $year, $month, $counter++);
                $basic = (float)$emp['basic_salary'];
                $housing = round($basic * 0.15, 2);
                $bonus = round($basic * 0.10, 2);
                $totAllowances = $housing + $bonus;

                $pension = round($basic * 0.08, 2);
                $tax = round($basic * 0.05, 2);
                $totDeductions = $pension + $tax;

                $net = ($basic + $totAllowances) - $totDeductions;

                $isPaid = ($empId === 1) ? 1 : 0;
                $payDate = ($empId === 1) ? '2026-11-18' : null;
                $payMethod = ($empId === 1) ? 'bankak' : 'bankak';
                $txRef = ($empId === 1) ? 'BNK-202611-884210' : null;
                $notes = ($empId === 1) ? 'تم تحويل الراتب عبر تطبيق بنكك (بنك الخرطوم)' : 'بانتظار الصرف الشهري';

                $db->query(
                    "INSERT INTO `payroll` (`payroll_number`, `employee_id`, `salary_month`, `salary_year`, `basic_salary`, `allowances`, `deductions`, `net_salary`, `is_paid`, `payment_date`, `payment_method`, `transaction_reference`, `notes`, `processed_by`)
                     VALUES (:num, :emp_id, :m, :y, :basic, :allow, :deduct, :net, :is_paid, :p_date, :p_method, :tx_ref, :notes, :proc_by)",
                    [
                        'num' => $payNum,
                        'emp_id' => $empId,
                        'm' => $month,
                        'y' => $year,
                        'basic' => $basic,
                        'allow' => $totAllowances,
                        'deduct' => $totDeductions,
                        'net' => $net,
                        'is_paid' => $isPaid,
                        'p_date' => $payDate,
                        'p_method' => $payMethod,
                        'tx_ref' => $txRef,
                        'notes' => $notes,
                        'proc_by' => $adminId,
                    ]
                );

                $payrollId = (int)$db->lastInsertId();

                // Insert items
                $db->query("INSERT INTO `payroll_items` (`payroll_id`, `type`, `title`, `amount`) VALUES (?, 'allowance', 'بدل سكن وانتقال (15%)', ?)", [$payrollId, $housing]);
                $db->query("INSERT INTO `payroll_items` (`payroll_id`, `type`, `title`, `amount`) VALUES (?, 'allowance', 'حافز طبي ومناوبات (10%)', ?)", [$payrollId, $bonus]);
                $db->query("INSERT INTO `payroll_items` (`payroll_id`, `type`, `title`, `amount`) VALUES (?, 'deduction', 'التأمين الاجتماعي والمعاشات (8%)', ?)", [$payrollId, $pension]);
                $db->query("INSERT INTO `payroll_items` (`payroll_id`, `type`, `title`, `amount`) VALUES (?, 'deduction', 'ضريبة كسب العمل (5%)', ?)", [$payrollId, $tax]);
            }
        }
    }
}
