<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Clinic;
use App\Models\Department;

class ClinicController extends Controller
{
    private Clinic $clinicModel;
    private Department $departmentModel;

    public function __construct()
    {
        $this->clinicModel = new Clinic();
        $this->departmentModel = new Department();
    }

    public function index(Request $request, Response $response): string
    {
        $clinics = $this->clinicModel->allWithDepartment();
        $departments = $this->departmentModel->where('type', 'medical');

        return $this->view('clinics/index', [
            'pageTitle' => 'العيادات الخارجية (OPD Clinics)',
            'clinics' => $clinics,
            'departments' => $departments,
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        if (!can('hospital.manage')) {
            Session::flash('error', 'ليس لديك صلاحية إضافة عيادة.');
            $this->redirect(url('/clinics'));
            return;
        }

        $validation = $this->validate($request->all(), [
            'department_id' => 'required',
            'room_number' => 'required',
            'name_ar' => 'required',
            'name_en' => 'required',
        ]);

        if (!$validation['isValid']) {
            $this->redirect(url('/clinics'));
            return;
        }

        $clinicId = $this->clinicModel->create([
            'department_id' => (int)$request->input('department_id'),
            'room_number' => trim((string)$request->input('room_number')),
            'name_ar' => trim((string)$request->input('name_ar')),
            'name_en' => trim((string)$request->input('name_en')),
            'is_active' => 1,
        ]);

        AuditLog::log('CREATE_CLINIC', 'clinics', $clinicId, null, $request->all());

        Session::flash('success', 'تم إنشاء العيادة بنجاح.');
        $this->redirect(url('/clinics'));
    }
}
