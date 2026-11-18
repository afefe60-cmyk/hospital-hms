<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\MedicationAdministration;
use App\Models\NursingCarePlan;
use App\Models\NursingNote;

class NursingController extends Controller
{
    private Admission $admissionModel;
    private NursingNote $noteModel;
    private NursingCarePlan $planModel;
    private MedicationAdministration $marModel;

    public function __construct()
    {
        $this->admissionModel = new Admission();
        $this->noteModel = new NursingNote();
        $this->planModel = new NursingCarePlan();
        $this->marModel = new MedicationAdministration();
    }

    public function index(Request $request, Response $response): string
    {
        $db = \App\Core\Database::getInstance();

        // 1. All currently admitted patients with beds
        $sqlInpatients = "SELECT a.*,
                                 p.mrn, p.gender, p.blood_group,
                                 CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                                 TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                                 b.bed_number, r.room_number, w.name_ar AS ward_name,
                                 d.name_ar AS department_name
                          FROM `admissions` a
                          INNER JOIN `patients` p ON a.patient_id = p.id
                          INNER JOIN `departments` d ON a.department_id = d.id
                          LEFT JOIN `bed_assignments` ba ON a.id = ba.admission_id AND ba.is_active = 1
                          LEFT JOIN `beds` b ON ba.bed_id = b.id
                          LEFT JOIN `rooms` r ON b.room_id = r.id
                          LEFT JOIN `wards` w ON r.ward_id = w.id
                          WHERE a.status = 'admitted'
                          ORDER BY w.id ASC, b.bed_number ASC";
        $inpatients = $db->fetchAll($sqlInpatients);

        // 2. Pending medications scheduled for today / shift
        $sqlMar = "SELECT m.*,
                          a.admission_number,
                          CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                          p.mrn,
                          b.bed_number, r.room_number, w.name_ar AS ward_name
                   FROM `medication_administrations` m
                   INNER JOIN `admissions` a ON m.admission_id = a.id
                   INNER JOIN `patients` p ON a.patient_id = p.id
                   LEFT JOIN `bed_assignments` ba ON a.id = ba.admission_id AND ba.is_active = 1
                   LEFT JOIN `beds` b ON ba.bed_id = b.id
                   LEFT JOIN `rooms` r ON b.room_id = r.id
                   LEFT JOIN `wards` w ON r.ward_id = w.id
                   WHERE a.status = 'admitted' AND m.status = 'pending'
                   ORDER BY m.scheduled_time ASC";
        $pendingMar = $db->fetchAll($sqlMar);

        return $this->view('nursing/index', [
            'pageTitle' => 'محطة التمريض السريرية وجدول إعطاء الأدوية (Nursing Station & MAR)',
            'inpatients' => $inpatients,
            'pendingMar' => $pendingMar,
        ]);
    }

    public function addNote(Request $request, Response $response, string $admissionId): void
    {
        $admission = $this->admissionModel->find($admissionId);
        if (!$admission) {
            Session::flash('error', 'سجل التنويم غير موجود.');
            $this->redirect(url('/nursing'));
            return;
        }

        $content = trim((string)$request->input('note_content'));
        $type = $request->input('note_type', 'routine');

        if (empty($content)) {
            Session::flash('error', 'يرجى كتابة نص الملاحظة التمريضية.');
            $this->redirect(url("/admissions/{$admissionId}"));
            return;
        }

        $noteId = $this->noteModel->create([
            'admission_id' => (int)$admissionId,
            'nurse_id' => auth()->id(),
            'note_type' => $type,
            'note_content' => $content,
            'recorded_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLog::log('ADD_NURSING_NOTE', 'nursing_notes', $noteId, null, ['type' => $type]);

        Session::flash('success', 'تم تدوين الملاحظة التمريضية بنجاح.');
        $this->redirect(url("/admissions/{$admissionId}"));
    }

    public function addCarePlan(Request $request, Response $response, string $admissionId): void
    {
        $admission = $this->admissionModel->find($admissionId);
        if (!$admission) {
            Session::flash('error', 'سجل التنويم غير موجود.');
            $this->redirect(url('/nursing'));
            return;
        }

        $diag = trim((string)$request->input('nursing_diagnosis'));
        $goals = trim((string)$request->input('goals'));
        $interventions = trim((string)$request->input('interventions'));

        if (empty($diag) || empty($goals) || empty($interventions)) {
            Session::flash('error', 'يرجى ملء جميع حقول خطة الرعاية التمريضية.');
            $this->redirect(url("/admissions/{$admissionId}"));
            return;
        }

        $planId = $this->planModel->create([
            'admission_id' => (int)$admissionId,
            'nursing_diagnosis' => $diag,
            'goals' => $goals,
            'interventions' => $interventions,
            'status' => 'active',
            'created_by' => auth()->id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLog::log('CREATE_NURSING_CARE_PLAN', 'nursing_care_plans', $planId);

        Session::flash('success', 'تم إنشاء خطة الرعاية التمريضية بنجاح.');
        $this->redirect(url("/admissions/{$admissionId}"));
    }

    public function recordMar(Request $request, Response $response, string $marId): void
    {
        $mar = $this->marModel->find($marId);
        if (!$mar) {
            Session::flash('error', 'سجل إعطاء الدواء غير موجود.');
            $this->redirect(url('/nursing'));
            return;
        }

        $status = $request->input('status', 'given');
        $notes = trim((string)$request->input('notes'));

        $this->marModel->update($marId, [
            'status' => $status,
            'administered_time' => ($status === 'given') ? date('Y-m-d H:i:s') : null,
            'administered_by' => auth()->id(),
            'notes' => $notes ?: $mar['notes'],
        ]);

        AuditLog::log('RECORD_MAR', 'medication_administrations', $marId, ['old' => $mar['status']], ['new' => $status]);

        Session::flash('success', "تم تسجيل حالة الجرعة الدوائية ({$status}) في سجل التمريض MAR بنجاح.");

        $referer = $_SERVER['HTTP_REFERER'] ?? url('/nursing');
        $this->redirect($referer);
    }

    public function addMarDose(Request $request, Response $response, string $admissionId): void
    {
        $admission = $this->admissionModel->find($admissionId);
        if (!$admission) {
            Session::flash('error', 'سجل التنويم غير موجود.');
            $this->redirect(url('/nursing'));
            return;
        }

        $medicine = trim((string)$request->input('medicine_name'));
        $dosage = trim((string)$request->input('dosage'));
        $route = $request->input('route', 'oral');
        $time = $request->input('scheduled_time') ?: date('Y-m-d H:i:s');

        if (empty($medicine) || empty($dosage)) {
            Session::flash('error', 'يرجى إدخال اسم الدواء والجرعة.');
            $this->redirect(url("/admissions/{$admissionId}"));
            return;
        }

        $this->marModel->create([
            'admission_id' => (int)$admissionId,
            'medicine_name' => $medicine,
            'dosage' => $dosage,
            'route' => $route,
            'scheduled_time' => $time,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLog::log('SCHEDULE_MAR_DOSE', 'medication_administrations', $admissionId, null, [
            'medicine' => $medicine,
            'dosage' => $dosage,
        ]);

        Session::flash('success', 'تمت جدولة الجرعة الدوائية بسجل إعطاء الأدوية (MAR) بنجاح.');
        $this->redirect(url("/admissions/{$admissionId}"));
    }
}
