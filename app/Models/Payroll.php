<?php

namespace App\Models;

use App\Core\Model;

class Payroll extends Model
{
    protected string $table = 'payroll';
    protected array $fillable = [
        'payroll_number',
        'employee_id',
        'salary_month',
        'salary_year',
        'basic_salary',
        'allowances',
        'deductions',
        'net_salary',
        'is_paid',
        'payment_date',
        'payment_method',
        'transaction_reference',
        'notes',
        'processed_by',
    ];

    /**
     * Generate unique payroll number: PAY-YYYYMM-XXXXX
     */
    public function generatePayrollNumber(int $year, int $month): string
    {
        $prefix = sprintf("PAY-%04d%02d-", $year, $month);
        $sql = "SELECT COUNT(*) as count FROM `payroll` WHERE `payroll_number` LIKE :prefix";
        $row = $this->db->fetchOne($sql, ['prefix' => $prefix . '%']);
        $seq = (int)($row['count'] ?? 0) + 1;
        return $prefix . str_pad((string)$seq, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get all payroll sheets with employee and department details
     */
    public function allDetailed(?int $year = null, ?int $month = null, ?int $departmentId = null, ?string $isPaid = null): array
    {
        $params = [];
        $where = [];

        if ($year !== null && $year > 0) {
            $where[] = "p.salary_year = :year";
            $params['year'] = $year;
        }

        if ($month !== null && $month > 0) {
            $where[] = "p.salary_month = :month";
            $params['month'] = $month;
        }

        if ($departmentId !== null && $departmentId > 0) {
            $where[] = "e.department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }

        if ($isPaid !== null && $isPaid !== '') {
            $where[] = "p.is_paid = :is_paid";
            $params['is_paid'] = (int)$isPaid;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT p.*,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS employee_name,
                       e.employee_code, e.job_title, e.national_id, e.phone,
                       d.name_ar AS department_name_ar,
                       u.full_name AS processor_name
                FROM `payroll` p
                INNER JOIN `employees` e ON p.employee_id = e.id
                INNER JOIN `departments` d ON e.department_id = d.id
                INNER JOIN `users` u ON p.processed_by = u.id
                {$whereClause}
                ORDER BY p.salary_year DESC, p.salary_month DESC, e.department_id ASC, e.first_name ASC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get single payroll sheet by ID with full employee info and line items
     */
    public function findDetailed(int $id): ?array
    {
        $sql = "SELECT p.*,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS employee_name,
                       e.employee_code, e.job_title, e.national_id, e.phone, e.email, e.hire_date,
                       d.name_ar AS department_name_ar,
                       u.full_name AS processor_name
                FROM `payroll` p
                INNER JOIN `employees` e ON p.employee_id = e.id
                INNER JOIN `departments` d ON e.department_id = d.id
                INNER JOIN `users` u ON p.processed_by = u.id
                WHERE p.id = :id
                LIMIT 1";

        $payroll = $this->db->fetchOne($sql, ['id' => $id]);
        if (!$payroll) {
            return null;
        }

        $itemModel = new PayrollItem();
        $payroll['items'] = $itemModel->byPayrollId($id);

        return $payroll;
    }

    /**
     * Calculate financial breakdown
     */
    public static function calculateBreakdown(float $basicSalary, array $allowances = [], array $deductions = []): array
    {
        $totalAllowances = 0.0;
        foreach ($allowances as $a) {
            $totalAllowances += (float)($a['amount'] ?? 0);
        }

        $totalDeductions = 0.0;
        foreach ($deductions as $d) {
            $totalDeductions += (float)($d['amount'] ?? 0);
        }

        $netSalary = max(0.0, ($basicSalary + $totalAllowances) - $totalDeductions);

        return [
            'basic_salary'     => round($basicSalary, 2),
            'total_allowances' => round($totalAllowances, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_salary'       => round($netSalary, 2),
        ];
    }

    /**
     * Batch generate monthly payroll sheets for active employees
     */
    public function generateMonthlyBatch(int $year, int $month, int $processedBy, ?int $departmentId = null): array
    {
        $deptClause = '';
        $params = [];
        if ($departmentId !== null && $departmentId > 0) {
            $deptClause = ' AND department_id = :dept_id';
            $params['dept_id'] = $departmentId;
        }

        $sql = "SELECT * FROM `employees` WHERE `status` = 'active' {$deptClause} ORDER BY `id` ASC";
        $employees = $this->db->fetchAll($sql, $params);

        $createdCount = 0;
        $skippedCount = 0;
        $itemModel = new PayrollItem();

        foreach ($employees as $emp) {
            $empId = (int)$emp['id'];

            // Check if already generated
            $checkSql = "SELECT id FROM `payroll` WHERE `employee_id` = :emp_id AND `salary_month` = :m AND `salary_year` = :y LIMIT 1";
            $exists = $this->db->fetchOne($checkSql, ['emp_id' => $empId, 'm' => $month, 'y' => $year]);
            if ($exists) {
                $skippedCount++;
                continue;
            }

            $basic = (float)$emp['basic_salary'];

            // Standard Sudanese Hospital HR structure:
            // Allowances:
            // 1. بدل سكن وترحيل (Housing & Transport) ~ 15%
            // 2. حافز طبي ومناوبات (Medical & On-call incentive) ~ 10%
            // Deductions:
            // 1. مساهمة المعاشات والتأمين الاجتماعي (Social Security 8%)
            // 2. ضريبة كسب العمل (Personal Income Tax 5%)
            $housingTransport = round($basic * 0.15, 2);
            $medicalIncentive = round($basic * 0.10, 2);
            $totalAllowances = $housingTransport + $medicalIncentive;

            $socialSecurity = round($basic * 0.08, 2);
            $incomeTax = round($basic * 0.05, 2);
            $totalDeductions = $socialSecurity + $incomeTax;

            $net = max(0.0, ($basic + $totalAllowances) - $totalDeductions);
            $payrollNum = $this->generatePayrollNumber($year, $month);

            $payrollId = $this->create([
                'payroll_number' => $payrollNum,
                'employee_id'   => $empId,
                'salary_month'  => $month,
                'salary_year'   => $year,
                'basic_salary'  => $basic,
                'allowances'    => $totalAllowances,
                'deductions'    => $totalDeductions,
                'net_salary'    => $net,
                'is_paid'       => 0,
                'processed_by'  => $processedBy,
                'notes'         => 'تم إنشاء المسير الشهري الآلي للنظام',
            ]);

            // Add standard line items
            $itemModel->create([
                'payroll_id' => $payrollId,
                'type'       => 'allowance',
                'title'      => 'بدل سكن وانتقال (15%)',
                'amount'     => $housingTransport,
            ]);

            $itemModel->create([
                'payroll_id' => $payrollId,
                'type'       => 'allowance',
                'title'      => 'حافز مناوبات وعيادات (10%)',
                'amount'     => $medicalIncentive,
            ]);

            $itemModel->create([
                'payroll_id' => $payrollId,
                'type'       => 'deduction',
                'title'      => 'التأمين الاجتماعي والمعاشات (8%)',
                'amount'     => $socialSecurity,
            ]);

            $itemModel->create([
                'payroll_id' => $payrollId,
                'type'       => 'deduction',
                'title'      => 'ضريبة كسب العمل (5%)',
                'amount'     => $incomeTax,
            ]);

            $createdCount++;
        }

        return [
            'created' => $createdCount,
            'skipped' => $skippedCount,
            'total'   => count($employees),
        ];
    }

    /**
     * Mark payroll as paid
     */
    public function markPaid(int $id, string $paymentMethod, ?string $transactionReference = null, ?string $notes = null): bool
    {
        $today = date('Y-m-d');
        return $this->update($id, [
            'is_paid'               => 1,
            'payment_date'          => $today,
            'payment_method'        => $paymentMethod,
            'transaction_reference' => $transactionReference,
            'notes'                 => $notes,
        ]);
    }

    /**
     * Overall payroll summary statistics
     */
    public function getSummaryStats(?int $year = null, ?int $month = null): array
    {
        $params = [];
        $where = [];

        if ($year !== null && $year > 0) {
            $where[] = "salary_year = :year";
            $params['year'] = $year;
        }

        if ($month !== null && $month > 0) {
            $where[] = "salary_month = :month";
            $params['month'] = $month;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT 
                    COUNT(*) as total_sheets,
                    COALESCE(SUM(basic_salary), 0) as sum_basic,
                    COALESCE(SUM(allowances), 0) as sum_allowances,
                    COALESCE(SUM(deductions), 0) as sum_deductions,
                    COALESCE(SUM(net_salary), 0) as sum_net,
                    COALESCE(SUM(CASE WHEN is_paid = 1 THEN net_salary ELSE 0 END), 0) as sum_paid,
                    COALESCE(SUM(CASE WHEN is_paid = 0 THEN net_salary ELSE 0 END), 0) as sum_unpaid,
                    SUM(CASE WHEN is_paid = 1 THEN 1 ELSE 0 END) as count_paid,
                    SUM(CASE WHEN is_paid = 0 THEN 1 ELSE 0 END) as count_unpaid
                FROM `payroll`
                {$whereClause}";

        return $this->db->fetchOne($sql, $params);
    }
}
