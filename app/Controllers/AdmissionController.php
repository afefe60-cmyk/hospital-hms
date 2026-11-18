<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Department;
use App\Models\DischargeRecord;
use App\Models\Doctor;
use App\Models\MedicationAdministration;
use App\Models\NursingCarePlan;
use App\Models\NursingNote;
use App\Models\Patient;

class AdmissionController extends Controller
{
    private Admission $admissionModel;
    private BedAssignment $bedAssignModel;
    private Bed $bedModel;
    private Patient $patientModel;
    private Doctor $doctorModel;
    private Department $deptModel;
    private NursingNote $noteModel;
    private NursingCarePlan $planModel;
    private MedicationAdministration $marModel;
    private DischargeRecord $dischargeModel;

    public function __construct()
    {
        $this->admissionModel = new Admission();
        $this->bedAssignModel = new BedAssignment();
        $this->bedModel = new Bed();
        $this->patientModel = new Patient();
        $this->doctorModel = new Doctor();
        $this->deptModel = new Department();
        $this->noteModel = new NursingNote();
        $this->planModel = new NursingCarePlan();
        $this->marModel = new MedicationAdministration();
        $this->dischargeModel = new DischargeRecord();
    }

    public function index(Request $request, Response $response): string
    {
        $db = \App\Core\Database::getInstance();
        $status = $request->query('status', 'admitted');

        $where = "WHERE 1=1";
        $params = [];

        if ($status !== 'all') {
            $where .= " AND a.status = :status";
            $params['status'] = $status;
        }

        $sql = "SELECT a.*,
                       p.mrn, p.gender,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                       d.name_ar AS department_name,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS doctor_name,
                       b.bed_number, r.room_number, w.name_ar AS ward_name
                FROM `admissions` a
                INNER JOIN `patients` p ON a.patient_id = p.id
                INNER JOIN `departments` d ON a.department_id = d.id
                INNER JOIN `doctors` doc ON a.admitting_doctor_id = doc.id
                INNER JOIN `employees` e ON doc.employee_id = e.id
                LEFT JOIN `bed_assignments` ba ON a.id = ba.admission_id AND ba.is_active = 1
                LEFT JOIN `beds` b ON ba.bed_id = b.id
                LEFT JOIN `rooms` r ON b.room_id = r.id
                LEFT JOIN `wards` w ON r.ward_id = w.id
                {$where}
                ORDER BY a.admission_date DESC";
        $admissions = $db->fetchAll($sql, $params);

        $counts = [
            'admitted' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `admissions` WHERE `status` = 'admitted'"),
            'discharged' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `admissions` WHERE `status` = 'discharged'"),
            'total' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `admissions`"),
        ];

        return $this->view('admissions/index', [
            'pageTitle' => 'إدارة التنويم والأقسام الداخلية (Inpatient Department - IPD)',
            'admissions' => $admissions,
            'status' => $status,
            'counts' => $counts,
        ]);
    }

    public function create(Request $request, Response $response): string
    {
        $patients = $this->patientModel->all('first_name', 'ASC');
        $doctors = $this->doctorModel->all();
        $departments = $this->deptModel->where('status', 'active');

        // Available beds
        $db = \App\Core\Database::getInstance();
        $sql = "SELECT b.*, r.room_number, r.room_type, w.name_ar AS ward_name
                FROM `beds` b
                INNER JOIN `rooms` r ON b.room_id = r.id
                INNER JOIN `wards` w ON r.ward_id = w.id
                WHERE b.status = 'available'
                ORDER BY w.id ASC, b.bed_number ASC";
        $availableBeds = $db->fetchAll($sql);

        $preselectedPatientId = $request->query('patient_id');
        $erCaseId = $request->query('er_case_id');

        return $this->view('admissions/create', [
            'pageTitle' => 'تسجيل إذن تنويم مريض جديد (Inpatient Admission)',
            'patients' => $patients,
            'doctors' => $doctors,
            'departments' => $departments,
            'availableBeds' => $availableBeds,
            'preselectedPatientId' => $preselectedPatientId,
            'erCaseId' => $erCaseId,
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $patientId = (int)$request->input('patient_id');
        $doctorId = (int)$request->input('admitting_doctor_id');
        $deptId = (int)$request->input('department_id');
        $bedId = (int)$request->input('bed_id');
        $erCaseId = $request->input('emergency_case_id') ? (int)$request->input('emergency_case_id') : null;
        $reason = trim((string)$request->input('admission_reason'));
        $diagnosis = trim((string)$request->input('provisional_diagnosis'));

        if ($patientId <= 0 || $doctorId <= 0 || $deptId <= 0 || $bedId <= 0) {
            Session::flash('error', 'يرجى استيفاء الحقول الإجبارية واختيار السرير المتاح للتسكين.');
            $this->redirect(url('/admissions/create'));
            return;
        }

        $admNumber = $this->admissionModel->generateAdmissionNumber();
        $admId = $this->admissionModel->create([
            'admission_number' => $admNumber,
            'patient_id' => $patientId,
            'admitting_doctor_id' => $doctorId,
            'department_id' => $deptId,
            'emergency_case_id' => $erCaseId,
            'admission_date' => date('Y-m-d H:i:s'),
            'admission_type' => $erCaseId ? 'emergency' : 'elective',
            'admission_reason' => $reason,
            'provisional_diagnosis' => $diagnosis,
            'status' => 'admitted',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Assign bed
        $this->bedAssignModel->assignBed((int)$admId, $bedId, 'تسكين عند إجراء التنويم');

        // If came from emergency, update ER status to 'admitted'
        if ($erCaseId) {
            $db = \App\Core\Database::getInstance();
            $db->query("UPDATE `emergency_cases` SET `status` = 'admitted' WHERE `id` = :id", ['id' => $erCaseId]);
        }

        AuditLog::log('CREATE_ADMISSION', 'admissions', $admId, null, [
            'admission_number' => $admNumber,
            'bed_id' => $bedId,
        ]);

        Session::flash('success', "تم تسجيل إجراء التنويم بنجاح بالرقم ({$admNumber}) وتسكين السرير.");
        $this->redirect(url("/admissions/{$admId}"));
    }

    public function show(Request $request, Response $response, string $id): string
    {
        $admission = $this->admissionModel->detailed((int)$id);
        if (!$admission) {
            Session::flash('error', 'سجل التنويم غير موجود.');
            $this->redirect(url('/admissions'));
        }

        $activeBed = $this->admissionModel->activeBed((int)$id);
        $notes = $this->noteModel->forAdmission((int)$id);
        $carePlans = $this->planModel->forAdmission((int)$id);
        $marList = $this->marModel->forAdmission((int)$id);
        $dischargeRecord = $this->dischargeModel->detailed((int)$id);

        // Available beds for transfer
        $db = \App\Core\Database::getInstance();
        $sql = "SELECT b.*, r.room_number, w.name_ar AS ward_name
                FROM `beds` b
                INNER JOIN `rooms` r ON b.room_id = r.id
                INNER JOIN `wards` w ON r.ward_id = w.id
                WHERE b.status = 'available'
                ORDER BY w.id ASC, b.bed_number ASC";
        $availableBeds = $db->fetchAll($sql);

        $doctors = $this->doctorModel->all();

        return $this->view('admissions/show', [
            'pageTitle' => "ملف التنويم: {$admission['patient_name']} ({$admission['admission_number']})",
            'admission' => $admission,
            'activeBed' => $activeBed,
            'notes' => $notes,
            'carePlans' => $carePlans,
            'marList' => $marList,
            'dischargeRecord' => $dischargeRecord,
            'availableBeds' => $availableBeds,
            'doctors' => $doctors,
        ]);
    }

    public function transferBed(Request $request, Response $response, string $id): void
    {
        $admission = $this->admissionModel->find($id);
        if (!$admission || $admission['status'] !== 'admitted') {
            Session::flash('error', 'لا يمكن تحويل السرير لحالة منتهية.');
            $this->redirect(url("/admissions/{$id}"));
            return;
        }

        $newBedId = (int)$request->input('new_bed_id');
        $notes = trim((string)$request->input('transfer_reason', 'تحويل سرير بطلب طبي/إداري'));

        if ($newBedId <= 0) {
            Session::flash('error', 'يرجى اختيار السرير الجديد.');
            $this->redirect(url("/admissions/{$id}"));
            return;
        }

        $this->bedAssignModel->assignBed((int)$id, $newBedId, $notes);

        AuditLog::log('TRANSFER_BED', 'admissions', $id, null, ['new_bed_id' => $newBedId, 'notes' => $notes]);

        Session::flash('success', 'تم تحويل ونقل المريض إلى السرير الجديد بنجاح وإخلاء السرير السابق للتطهير.');
        $this->redirect(url("/admissions/{$id}"));
    }

    public function discharge(Request $request, Response $response, string $id): void
    {
        $admission = $this->admissionModel->find($id);
        if (!$admission || $admission['status'] !== 'admitted') {
            Session::flash('error', 'سجل التنويم مغلق مسبقاً.');
            $this->redirect(url("/admissions/{$id}"));
            return;
        }

        $summary = trim((string)$request->input('discharge_summary'));
        $condition = $request->input('discharge_condition', 'improved');
        $dischargeType = $request->input('discharge_type', 'normal');
        $doctorId = (int)$request->input('discharge_doctor_id');

        if (empty($summary) || $doctorId <= 0) {
            Session::flash('error', 'يرجى ملء ملخص الخروج واختيار الطبيب المعتمد للخروج.');
            $this->redirect(url("/admissions/{$id}"));
            return;
        }

        $now = date('Y-m-d H:i:s');

        // Create discharge record
        $this->dischargeModel->create([
            'admission_id' => (int)$id,
            'discharge_type' => $dischargeType,
            'discharge_summary' => $summary,
            'discharge_condition' => $condition,
            'discharge_doctor_id' => $doctorId,
            'discharged_at' => $now,
            'created_at' => $now,
        ]);

        // Release Bed to 'cleaning'
        $this->bedAssignModel->releaseBed((int)$id);

        // Update Admission
        $this->admissionModel->update($id, [
            'status' => 'discharged',
            'discharge_date' => $now,
        ]);

        AuditLog::log('DISCHARGE_PATIENT', 'admissions', $id, null, [
            'condition' => $condition,
            'doctor_id' => $doctorId,
        ]);

        Session::flash('success', 'تم اعتماد خروج المريض وإخلاء السرير لقسم النظافة والتعقيم بنجاح.');
        $this->redirect(url("/admissions/{$id}"));
    }
}
