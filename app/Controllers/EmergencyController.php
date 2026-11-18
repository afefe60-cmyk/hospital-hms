<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\EmergencyCase;
use App\Models\Patient;
use App\Models\TriageAssessment;

class EmergencyController extends Controller
{
    private EmergencyCase $erModel;
    private TriageAssessment $triageModel;
    private Patient $patientModel;
    private Doctor $doctorModel;

    public function __construct()
    {
        $this->erModel = new EmergencyCase();
        $this->triageModel = new TriageAssessment();
        $this->patientModel = new Patient();
        $this->doctorModel = new Doctor();
    }

    public function index(Request $request, Response $response): string
    {
        $db = \App\Core\Database::getInstance();
        $status = $request->query('status', 'active');

        $where = "WHERE 1=1";
        $params = [];

        if ($status === 'active') {
            $where .= " AND er.status IN ('triaged', 'under_treatment')";
        } elseif ($status !== 'all') {
            $where .= " AND er.status = :status";
            $params['status'] = $status;
        }

        $sql = "SELECT er.*,
                       p.mrn, p.gender,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS registered_patient_name,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                       ta.gcs_score, ta.systolic_bp, ta.diastolic_bp, ta.pulse_rate, ta.oxygen_saturation,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS doctor_name
                FROM `emergency_cases` er
                LEFT JOIN `patients` p ON er.patient_id = p.id
                LEFT JOIN `triage_assessments` ta ON er.id = ta.emergency_case_id
                LEFT JOIN `doctors` d ON er.attending_doctor_id = d.id
                LEFT JOIN `employees` e ON d.employee_id = e.id
                {$where}
                ORDER BY FIELD(er.triage_level, 'red', 'orange', 'yellow', 'green', 'blue') ASC, er.arrival_time DESC";
        $cases = $db->fetchAll($sql, $params);

        // Calculate counts by triage color for active cases
        $triageCounts = [
            'red' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `emergency_cases` WHERE `status` IN ('triaged', 'under_treatment') AND `triage_level` = 'red'"),
            'orange' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `emergency_cases` WHERE `status` IN ('triaged', 'under_treatment') AND `triage_level` = 'orange'"),
            'yellow' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `emergency_cases` WHERE `status` IN ('triaged', 'under_treatment') AND `triage_level` = 'yellow'"),
            'green' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `emergency_cases` WHERE `status` IN ('triaged', 'under_treatment') AND `triage_level` = 'green'"),
            'total' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `emergency_cases` WHERE `status` IN ('triaged', 'under_treatment')"),
        ];

        return $this->view('emergency/index', [
            'pageTitle' => 'قسم الطوارئ والفرز السريري (Emergency Department & Triage)',
            'cases' => $cases,
            'status' => $status,
            'triageCounts' => $triageCounts,
        ]);
    }

    public function create(Request $request, Response $response): string
    {
        $patients = $this->patientModel->all('first_name', 'ASC');
        $doctors = $this->doctorModel->all();

        return $this->view('emergency/create', [
            'pageTitle' => 'تسجيل حالة طوارئ جديدة وفرز أولي',
            'patients' => $patients,
            'doctors' => $doctors,
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $patientId = $request->input('patient_id') ? (int)$request->input('patient_id') : null;
        $tempName = trim((string)$request->input('temporary_patient_name'));
        $complaint = trim((string)$request->input('chief_complaint'));
        $triageColor = $request->input('triage_level', 'yellow');
        $arrivalMode = $request->input('arrival_mode', 'walk_in');
        $doctorId = $request->input('attending_doctor_id') ? (int)$request->input('attending_doctor_id') : null;

        if (!$patientId && empty($tempName)) {
            Session::flash('error', 'يرجى تحديد المريض المسجل أو إدخال وصف للحالة غير محددة الهوية (Trauma Case).');
            $this->redirect(url('/emergency/create'));
            return;
        }

        if (empty($complaint)) {
            Session::flash('error', 'يرجى إدخال الشكوى الرئيسية أو سبب وصول الطوارئ.');
            $this->redirect(url('/emergency/create'));
            return;
        }

        $caseNumber = $this->erModel->generateCaseNumber();
        $caseId = $this->erModel->create([
            'case_number' => $caseNumber,
            'patient_id' => $patientId,
            'temporary_patient_name' => $tempName ?: null,
            'arrival_mode' => $arrivalMode,
            'arrival_time' => date('Y-m-d H:i:s'),
            'triage_level' => $triageColor,
            'chief_complaint' => $complaint,
            'attending_doctor_id' => $doctorId,
            'status' => 'triaged',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Record Initial Triage Assessment
        $this->triageModel->create([
            'emergency_case_id' => $caseId,
            'triage_color' => $triageColor,
            'gcs_score' => $request->input('gcs_score') ? (int)$request->input('gcs_score') : null,
            'airway_status' => $request->input('airway_status', 'clear'),
            'breathing_status' => $request->input('breathing_status', 'normal'),
            'circulation_status' => $request->input('circulation_status', 'stable'),
            'systolic_bp' => $request->input('systolic_bp') ? (int)$request->input('systolic_bp') : null,
            'diastolic_bp' => $request->input('diastolic_bp') ? (int)$request->input('diastolic_bp') : null,
            'pulse_rate' => $request->input('pulse_rate') ? (int)$request->input('pulse_rate') : null,
            'respiratory_rate' => $request->input('respiratory_rate') ? (int)$request->input('respiratory_rate') : null,
            'temperature' => $request->input('temperature') ? (float)$request->input('temperature') : null,
            'oxygen_saturation' => $request->input('oxygen_saturation') ? (int)$request->input('oxygen_saturation') : null,
            'pain_score' => $request->input('pain_score') ? (int)$request->input('pain_score') : null,
            'triage_nurse_id' => auth()->id(),
            'notes' => trim((string)$request->input('triage_notes')),
            'assessed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLog::log('CREATE_EMERGENCY_CASE', 'emergency_cases', $caseId, null, [
            'case_number' => $caseNumber,
            'triage' => $triageColor,
        ]);

        Session::flash('success', "تم تسجيل حالة الطوارئ بنجاح برقم ({$caseNumber}) وفئة الفرز ({$triageColor}).");
        $this->redirect(url("/emergency/{$caseId}"));
    }

    public function show(Request $request, Response $response, string $id): string
    {
        $case = $this->erModel->detailed((int)$id);
        if (!$case) {
            Session::flash('error', 'حالة الطوارئ غير موجودة.');
            $this->redirect(url('/emergency'));
        }

        $triage = $this->erModel->triageAssessment((int)$id);
        $doctors = $this->doctorModel->all();

        return $this->view('emergency/show', [
            'pageTitle' => "ملف حالة طوارئ - {$case['case_number']}",
            'case' => $case,
            'triage' => $triage,
            'doctors' => $doctors,
        ]);
    }

    public function updateStatus(Request $request, Response $response, string $id): void
    {
        $case = $this->erModel->find($id);
        if (!$case) {
            Session::flash('error', 'حالة الطوارئ غير موجودة.');
            $this->redirect(url('/emergency'));
            return;
        }

        $status = $request->input('status');
        $notes = trim((string)$request->input('outcome_notes'));

        $this->erModel->update($id, [
            'status' => $status,
            'outcome_notes' => $notes ?: $case['outcome_notes'],
        ]);

        AuditLog::log('UPDATE_EMERGENCY_STATUS', 'emergency_cases', $id, ['old' => $case['status']], ['new' => $status]);

        Session::flash('success', 'تم تحديث حالة الحالة الطارئة بنجاح.');
        $this->redirect(url("/emergency/{$id}"));
    }
}
