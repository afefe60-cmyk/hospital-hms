<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\Employee;
use App\Models\Department;
use App\Models\EmployeeRoster;
use App\Models\EmployeeLeave;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\AuditLog;

class HrController extends Controller
{
    private Employee $employeeModel;
    private Department $departmentModel;
    private EmployeeRoster $rosterModel;
    private EmployeeLeave $leaveModel;
    private Payroll $payrollModel;
    private PayrollItem $payrollItemModel;
    private Database $db;

    public function __construct()
    {
        $this->employeeModel = new Employee();
        $this->departmentModel = new Department();
        $this->rosterModel = new EmployeeRoster();
        $this->leaveModel = new EmployeeLeave();
        $this->payrollModel = new Payroll();
        $this->payrollItemModel = new PayrollItem();
        $this->db = Database::getInstance();
    }

    /**
     * HR & Payroll Dashboard
     */
    public function index(Request $request, Response $response): string
    {
        $currentMonth = (int)date('m');
        $currentYear = (int)date('Y');
        $today = date('Y-m-d');

        // Stats
        $totalEmployees = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM `employees` WHERE `status` = 'active'");
        $onDutyToday = $this->rosterModel->countOnDutyToday();
        $onLeaveToday = $this->leaveModel->countOnLeaveToday();
        $payrollStats = $this->payrollModel->getSummaryStats($currentYear, $currentMonth);

        // Recent items
        $todayRoster = $this->rosterModel->getByDate($today);
        $pendingLeaves = $this->leaveModel->allDetailed('pending');
        $recentPayroll = $this->payrollModel->allDetailed($currentYear, $currentMonth);

        return $this->view('hr/index', [
            'totalEmployees' => $totalEmployees,
            'onDutyToday'    => $onDutyToday,
            'onLeaveToday'   => $onLeaveToday,
            'payrollStats'   => $payrollStats,
            'todayRoster'    => $todayRoster,
            'pendingLeaves'  => array_slice($pendingLeaves, 0, 5),
            'recentPayroll'  => array_slice($recentPayroll, 0, 8),
            'currentMonth'   => $currentMonth,
            'currentYear'    => $currentYear,
        ]);
    }

    /**
     * Staff Rosters & Duty Schedules View
     */
    public function roster(Request $request, Response $response): string
    {
        $startDate = $request->query('start_date') ?: date('Y-m-d', strtotime('-2 days'));
        $endDate = $request->query('end_date') ?: date('Y-m-d', strtotime('+4 days'));
        $departmentId = $request->query('department_id') ? (int)$request->query('department_id') : null;

        $rosterList = $this->rosterModel->getRosterRange($startDate, $endDate, $departmentId);
        $departments = $this->departmentModel->all('name_ar', 'ASC');
        $employees = $this->employeeModel->allWithDepartment();
        $shifts = $this->db->fetchAll("SELECT * FROM `shifts` ORDER BY `id` ASC");

        return $this->view('hr/roster', [
            'rosterList'   => $rosterList,
            'departments'  => $departments,
            'employees'    => $employees,
            'shifts'       => $shifts,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'departmentId' => $departmentId,
        ]);
    }

    /**
     * Store or update duty roster shift
     */
    public function storeRoster(Request $request, Response $response): void
    {
        $employeeId = (int)$request->input('employee_id');
        $shiftId = (int)$request->input('shift_id');
        $departmentId = (int)$request->input('department_id');
        $dutyDate = trim((string)$request->input('duty_date'));
        $dutyRole = trim((string)$request->input('duty_role'));
        $notes = trim((string)$request->input('notes'));

        if (!$employeeId || !$shiftId || !$departmentId || empty($dutyDate)) {
            Session::flash('error', 'يرجى تعبئة جميع الحقول الإلزامية للمناوبة.');
            $response->redirect('/hr/roster');
            return;
        }

        $this->rosterModel->assignShift($employeeId, $shiftId, $departmentId, $dutyDate, $dutyRole, $notes);

        AuditLog::log(
            Session::get('user_id', 1),
            'assign_roster_shift',
            'employee_rosters',
            $employeeId,
            ['shift_id' => $shiftId, 'duty_date' => $dutyDate]
        );

        Session::flash('success', 'تم جدولة وردية الموظف في الجدول بنجاح.');
        $response->redirect('/hr/roster');
    }

    /**
     * Update roster attendance / duty status
     */
    public function updateRosterStatus(Request $request, Response $response): void
    {
        $id = (int)$request->input('id');
        $status = trim((string)$request->input('status'));
        $notes = trim((string)$request->input('notes'));

        $validStatuses = ['scheduled', 'attended', 'absent', 'on_leave', 'swapped'];
        if ($id <= 0 || !in_array($status, $validStatuses, true)) {
            Session::flash('error', 'بيانات حالة المناوبة غير صالحة.');
            $response->redirect('/hr/roster');
            return;
        }

        $data = ['status' => $status];
        if (!empty($notes)) {
            $data['notes'] = $notes;
        }

        $this->rosterModel->update($id, $data);
        Session::flash('success', 'تم تحديث حالة مناوبة الموظف بنجاح.');
        $response->redirect('/hr/roster');
    }

    /**
     * Employee Leaves Management
     */
    public function leaves(Request $request, Response $response): string
    {
        $status = $request->query('status');
        if ($status === 'all' || empty($status)) {
            $status = null;
        }

        $leaves = $this->leaveModel->allDetailed($status);
        $employees = $this->employeeModel->allWithDepartment();

        return $this->view('hr/leaves', [
            'leaves'         => $leaves,
            'employees'      => $employees,
            'selectedStatus' => $status,
        ]);
    }

    /**
     * Submit Leave Request
     */
    public function storeLeave(Request $request, Response $response): void
    {
        $employeeId = (int)$request->input('employee_id');
        $leaveType = trim((string)$request->input('leave_type'));
        $startDate = trim((string)$request->input('start_date'));
        $endDate = trim((string)$request->input('end_date'));
        $reason = trim((string)$request->input('reason'));

        if (!$employeeId || empty($startDate) || empty($endDate) || empty($reason)) {
            Session::flash('error', 'يرجى استيفاء كافة بيانات طلب الإجازة.');
            $response->redirect('/hr/leaves');
            return;
        }

        $start = strtotime($startDate);
        $end = strtotime($endDate);
        if ($end < $start) {
            Session::flash('error', 'تاريخ نهاية الإجازة لا يمكن أن يكون قبل تاريخ البداية.');
            $response->redirect('/hr/leaves');
            return;
        }

        $totalDays = (int)ceil(($end - $start) / 86400) + 1;

        $id = $this->leaveModel->create([
            'employee_id' => $employeeId,
            'leave_type'  => $leaveType,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'total_days'  => $totalDays,
            'reason'      => $reason,
            'status'      => 'pending',
            'approved_by' => null,
        ]);

        AuditLog::log(
            Session::get('user_id', 1),
            'create_leave_request',
            'employee_leaves',
            (int)$id,
            ['employee_id' => $employeeId, 'days' => $totalDays, 'type' => $leaveType]
        );

        Session::flash('success', 'تم تقديم طلب الإجازة بنجاح وهو بانتظار الاعتماد الإداري.');
        $response->redirect('/hr/leaves');
    }

    /**
     * Approve or Reject Leave Request
     */
    public function updateLeaveStatus(Request $request, Response $response): void
    {
        $id = (int)$request->input('id');
        $action = trim((string)$request->input('status')); // 'approved' or 'rejected'

        if ($id <= 0 || !in_array($action, ['approved', 'rejected'], true)) {
            Session::flash('error', 'إجراء الإجازة غير صالح.');
            $response->redirect('/hr/leaves');
            return;
        }

        $userId = Session::get('user_id', 1);
        $this->leaveModel->update($id, [
            'status'      => $action,
            'approved_by' => $action === 'approved' ? $userId : null,
        ]);

        AuditLog::log(
            $userId,
            'update_leave_status',
            'employee_leaves',
            $id,
            ['status' => $action]
        );

        $msg = ($action === 'approved') ? 'تم اعتماد وتأكيد طلب الإجازة بنجاح.' : 'تم رفض طلب الإجازة.';
        Session::flash('success', $msg);
        $response->redirect('/hr/leaves');
    }

    /**
     * Monthly Payroll Master Sheets List
     */
    public function payroll(Request $request, Response $response): string
    {
        $year = $request->query('year') ? (int)$request->query('year') : (int)date('Y');
        $month = $request->query('month') ? (int)$request->query('month') : (int)date('m');
        $departmentId = $request->query('department_id') ? (int)$request->query('department_id') : null;
        $isPaid = $request->query('is_paid');

        $payrollList = $this->payrollModel->allDetailed($year, $month, $departmentId, $isPaid);
        $summary = $this->payrollModel->getSummaryStats($year, $month);
        $departments = $this->departmentModel->all('name_ar', 'ASC');

        return $this->view('hr/payroll', [
            'payrollList'    => $payrollList,
            'summary'        => $summary,
            'departments'    => $departments,
            'selectedYear'   => $year,
            'selectedMonth'  => $month,
            'selectedDept'   => $departmentId,
            'selectedPaid'   => $isPaid,
        ]);
    }

    /**
     * Batch Generate Payroll Sheets for active employees
     */
    public function generatePayroll(Request $request, Response $response): void
    {
        $year = (int)$request->input('year');
        $month = (int)$request->input('month');
        $departmentId = $request->input('department_id') ? (int)$request->input('department_id') : null;

        if ($year < 2020 || $month < 1 || $month > 12) {
            Session::flash('error', 'يرجى اختيار شهر وسنة صالحين لإنشاء مسير الرواتب.');
            $response->redirect('/hr/payroll');
            return;
        }

        $userId = Session::get('user_id', 1);
        $result = $this->payrollModel->generateMonthlyBatch($year, $month, $userId, $departmentId);

        AuditLog::log(
            $userId,
            'batch_generate_payroll',
            'payroll',
            null,
            ['year' => $year, 'month' => $month, 'created' => $result['created'], 'skipped' => $result['skipped']]
        );

        $msg = sprintf(
            'تم إنشاء مسير رواتب الشهر بنجاح: تم إنشاء %d كشف جديد، وتخطي %d كشف موجود مسبقاً (إجمالي الموظفين: %d).',
            $result['created'],
            $result['skipped'],
            $result['total']
        );

        Session::flash('success', $msg);
        $response->redirect("/hr/payroll?year={$year}&month={$month}");
    }

    /**
     * Show Detailed Employee Payslip
     */
    public function showPayroll(Request $request, Response $response, string $id): string
    {
        $payroll = $this->payrollModel->findDetailed((int)$id);
        if (!$payroll) {
            Session::flash('error', 'كشف المرتب المطلوب غير موجود.');
            $response->redirect('/hr/payroll');
            return '';
        }

        return $this->view('hr/show_payroll', [
            'payroll' => $payroll,
        ]);
    }

    /**
     * Mark Employee Salary as Paid
     */
    public function paySalary(Request $request, Response $response): void
    {
        $id = (int)$request->input('id');
        $paymentMethod = trim((string)$request->input('payment_method', 'bankak'));
        $transactionReference = trim((string)$request->input('transaction_reference'));
        $notes = trim((string)$request->input('notes'));

        $payroll = $this->payrollModel->findDetailed($id);
        if (!$payroll) {
            Session::flash('error', 'كشف المرتب غير موجود.');
            $response->redirect('/hr/payroll');
            return;
        }

        if ($payroll['is_paid']) {
            Session::flash('info', 'هذا الراتب مسدد بالفعل مسبقاً.');
            $response->redirect("/hr/payroll/{$id}");
            return;
        }

        $this->payrollModel->markPaid($id, $paymentMethod, $transactionReference, $notes);

        AuditLog::log(
            Session::get('user_id', 1),
            'pay_salary',
            'payroll',
            $id,
            ['method' => $paymentMethod, 'reference' => $transactionReference, 'net_salary' => $payroll['net_salary']]
        );

        Session::flash('success', 'تم تسجيل صرف الراتب بنجاح وتحديث حالة الكشف إلى مسدد.');
        $response->redirect("/hr/payroll/{$id}");
    }

    /**
     * Printable Employee Payslip (كشف مفردات مرتب شهري)
     */
    public function printPayslip(Request $request, Response $response, string $id): string
    {
        $payroll = $this->payrollModel->findDetailed((int)$id);
        if (!$payroll) {
            Session::flash('error', 'كشف المرتب غير موجود.');
            $response->redirect('/hr/payroll');
            return '';
        }

        return $this->view('hr/print_payslip', [
            'payroll' => $payroll,
        ], null); // Render without main layout
    }

    /**
     * Printable Monthly Master Payroll Sheet (مسير الرواتب المجمع)
     */
    public function printPayrollSheet(Request $request, Response $response): string
    {
        $year = $request->query('year') ? (int)$request->query('year') : (int)date('Y');
        $month = $request->query('month') ? (int)$request->query('month') : (int)date('m');
        $departmentId = $request->query('department_id') ? (int)$request->query('department_id') : null;

        $payrollList = $this->payrollModel->allDetailed($year, $month, $departmentId);
        $summary = $this->payrollModel->getSummaryStats($year, $month);
        
        $deptName = 'كافة الأقسام الطبية والإدارية';
        if ($departmentId) {
            $dept = $this->departmentModel->find($departmentId);
            if ($dept) {
                $deptName = $dept['name_ar'];
            }
        }

        return $this->view('hr/print_payroll_sheet', [
            'payrollList'    => $payrollList,
            'summary'        => $summary,
            'deptName'       => $deptName,
            'selectedYear'   => $year,
            'selectedMonth'  => $month,
        ], null); // Render without main layout
    }
}
