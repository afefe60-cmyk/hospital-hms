<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Employee;

class EmployeeController extends Controller
{
    private Employee $employeeModel;
    private Department $departmentModel;

    public function __construct()
    {
        $this->employeeModel = new Employee();
        $this->departmentModel = new Department();
    }

    public function index(Request $request, Response $response): string
    {
        $employees = $this->employeeModel->allWithDepartment();
        $departments = $this->departmentModel->all();

        return $this->view('employees/index', [
            'pageTitle' => 'شؤون الموظفين والكوادر',
            'employees' => $employees,
            'departments' => $departments,
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        if (!can('hospital.manage')) {
            Session::flash('error', 'ليس لديك صلاحية إضافة موظف.');
            $this->redirect(url('/employees'));
            return;
        }

        $validation = $this->validate($request->all(), [
            'employee_code' => 'required',
            'first_name' => 'required',
            'second_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'national_id' => 'required',
            'phone' => 'required',
            'department_id' => 'required',
            'job_title' => 'required',
            'hire_date' => 'required',
        ]);

        if (!$validation['isValid']) {
            $this->redirect(url('/employees'));
            return;
        }

        $empCode = strtoupper(trim((string)$request->input('employee_code')));
        $natId = trim((string)$request->input('national_id'));

        $existing = $this->employeeModel->firstWhere('employee_code', $empCode);
        if ($existing) {
            Session::flash('error', 'الرقم الوظيفي مسجل مسبقاً لموظف آخر.');
            $this->redirect(url('/employees'));
            return;
        }

        $empId = $this->employeeModel->create([
            'employee_code' => $empCode,
            'first_name' => trim((string)$request->input('first_name')),
            'second_name' => trim((string)$request->input('second_name')),
            'last_name' => trim((string)$request->input('last_name')),
            'gender' => $request->input('gender'),
            'national_id' => $natId,
            'phone' => trim((string)$request->input('phone')),
            'email' => trim((string)$request->input('email')),
            'department_id' => (int)$request->input('department_id'),
            'job_title' => trim((string)$request->input('job_title')),
            'hire_date' => $request->input('hire_date'),
            'basic_salary' => (float)$request->input('basic_salary', 0.00),
            'status' => 'active',
        ]);

        AuditLog::log('CREATE_EMPLOYEE', 'employees', $empId, null, $request->all());

        Session::flash('success', 'تم تسجيل الموظف بنجاح.');
        $this->redirect(url('/employees'));
    }
}
