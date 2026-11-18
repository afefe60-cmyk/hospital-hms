<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Department;

class DepartmentController extends Controller
{
    private Department $departmentModel;

    public function __construct()
    {
        $this->departmentModel = new Department();
    }

    public function index(Request $request, Response $response): string
    {
        $departments = $this->departmentModel->all('id', 'ASC');

        return $this->view('departments/index', [
            'pageTitle' => 'الهيكل الإداري والأقسام الطبية',
            'departments' => $departments,
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        if (!can('hospital.manage')) {
            Session::flash('error', 'ليس لديك صلاحية إدارة الأقسام.');
            $this->redirect(url('/departments'));
            return;
        }

        $validation = $this->validate($request->all(), [
            'code' => 'required',
            'name_ar' => 'required',
            'name_en' => 'required',
            'type' => 'required',
        ]);

        if (!$validation['isValid']) {
            $this->redirect(url('/departments'));
            return;
        }

        $code = strtoupper(trim((string)$request->input('code')));
        $existing = $this->departmentModel->firstWhere('code', $code);
        if ($existing) {
            Session::flash('error', 'كود القسم مسجل مسبقاً.');
            $this->redirect(url('/departments'));
            return;
        }

        $depId = $this->departmentModel->create([
            'code' => $code,
            'name_ar' => trim((string)$request->input('name_ar')),
            'name_en' => trim((string)$request->input('name_en')),
            'type' => $request->input('type'),
            'is_active' => 1,
        ]);

        AuditLog::log('CREATE_DEPARTMENT', 'departments', $depId, null, $request->all());

        Session::flash('success', 'تم إنشاء القسم بنجاح.');
        $this->redirect(url('/departments'));
    }

    public function toggleStatus(Request $request, Response $response, string $id): void
    {
        if (!can('hospital.manage')) {
            Session::flash('error', 'غير مصرح.');
            $this->redirect(url('/departments'));
            return;
        }

        $department = $this->departmentModel->find($id);
        if ($department) {
            $newStatus = $department['is_active'] ? 0 : 1;
            $this->departmentModel->update($id, ['is_active' => $newStatus]);
            AuditLog::log('TOGGLE_DEPARTMENT_STATUS', 'departments', $id, ['is_active' => $department['is_active']], ['is_active' => $newStatus]);
            Session::flash('success', 'تم تحديث حالة القسم.');
        }

        $this->redirect(url('/departments'));
    }
}
