<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\Surgery;
use App\Models\AnesthesiaRecord;
use App\Models\OperatingRoom;
use App\Models\Patient;
use App\Models\AuditLog;

class SurgeryController extends Controller
{
    private Surgery $surgeryModel;
    private AnesthesiaRecord $anesthesiaModel;
    private Database $db;

    public function __construct()
    {
        $this->surgeryModel = new Surgery();
        $this->anesthesiaModel = new AnesthesiaRecord();
        $this->db = Database::getInstance();
    }

    public function index(Request $request, Response $response): string
    {
        $status = $request->query('status');
        $date = $request->query('date');
        $orId = $request->query('or_id');

        $surgeries = $this->surgeryModel->listWithDetails($status, $date);

        // Filter by OR if specified
        if ($orId) {
            $surgeries = array_filter($surgeries, fn($s) => (int)$s['operating_room_id'] === (int)$orId);
        }

        // Operating Rooms Status
        $operatingRooms = $this->db->fetchAll("SELECT op.*,
            (SELECT s.procedure_name FROM surgeries s WHERE s.operating_room_id = op.id AND s.status = 'in_progress' LIMIT 1) AS current_procedure,
            (SELECT CONCAT(ed.first_name, ' ', ed.last_name) FROM surgeries s JOIN doctors d ON s.lead_surgeon_id = d.id JOIN employees ed ON d.employee_id = ed.id WHERE s.operating_room_id = op.id AND s.status = 'in_progress' LIMIT 1) AS current_surgeon
            FROM operating_rooms op ORDER BY op.id ASC");

        // Metrics
        $totalToday = $this->db->fetchColumn("SELECT COUNT(*) FROM surgeries WHERE DATE(scheduled_start) = CURDATE()");
        $inProgress = $this->db->fetchColumn("SELECT COUNT(*) FROM surgeries WHERE status = 'in_progress'");
        $completedToday = $this->db->fetchColumn("SELECT COUNT(*) FROM surgeries WHERE status = 'completed' AND DATE(actual_end) = CURDATE()");
        $scheduledFuture = $this->db->fetchColumn("SELECT COUNT(*) FROM surgeries WHERE status = 'scheduled' AND scheduled_start >= NOW()");

        return $this->view('surgery/index', [
            'surgeries' => $surgeries,
            'operatingRooms' => $operatingRooms,
            'stats' => [
                'total_today' => (int)$totalToday,
                'in_progress' => (int)$inProgress,
                'completed_today' => (int)$completedToday,
                'scheduled_future' => (int)$scheduledFuture,
            ],
            'selectedStatus' => $status,
            'selectedDate' => $date,
            'selectedOr' => $orId,
        ]);
    }

    public function create(Request $request, Response $response): string
    {
        $patientId = $request->query('patient_id');
        $patient = null;
        if ($patientId) {
            $patientModel = new Patient();
            $patient = $patientModel->find((int)$patientId);
        }

        $operatingRooms = $this->db->fetchAll("SELECT * FROM operating_rooms ORDER BY id ASC");
        
        // Surgeons
        $surgeons = $this->db->fetchAll("SELECT d.id, CONCAT(e.first_name, ' ', e.last_name) AS name, s.name_ar AS specialty_name
            FROM doctors d
            JOIN employees e ON d.employee_id = e.id
            JOIN specialties s ON d.specialty_id = s.id
            ORDER BY e.first_name ASC");

        // Anesthesiologists
        $anesthesiologists = $this->db->fetchAll("SELECT d.id, CONCAT(e.first_name, ' ', e.last_name) AS name, s.name_ar AS specialty_name
            FROM doctors d
            JOIN employees e ON d.employee_id = e.id
            JOIN specialties s ON d.specialty_id = s.id
            WHERE s.code = 'SPEC-ANES' OR s.name_en LIKE '%Anesthesia%'
            ORDER BY e.first_name ASC");
        if (empty($anesthesiologists)) {
            $anesthesiologists = $surgeons;
        }

        // Scrub Nurses
        $nurses = $this->db->fetchAll("SELECT id, CONCAT(first_name, ' ', last_name) AS name, job_title
            FROM employees
            WHERE job_title LIKE '%تمريض%' OR job_title LIKE '%nurse%'
            ORDER BY first_name ASC");

        $patients = $this->db->fetchAll("SELECT id, mrn, CONCAT(first_name, ' ', second_name, ' ', last_name) AS name, phone_primary FROM patients ORDER BY id DESC LIMIT 50");

        return $this->view('surgery/create', [
            'patient' => $patient,
            'patients' => $patients,
            'operatingRooms' => $operatingRooms,
            'surgeons' => $surgeons,
            'anesthesiologists' => $anesthesiologists,
            'nurses' => $nurses,
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $patientId = (int)$request->input('patient_id');
        $orId = (int)$request->input('operating_room_id');
        $leadSurgeonId = (int)$request->input('lead_surgeon_id');
        $procedureName = trim($request->input('procedure_name', ''));
        $surgeryType = $request->input('surgery_type', 'elective');
        $scheduledStart = $request->input('scheduled_start');
        $scheduledEnd = $request->input('scheduled_end');

        if (!$patientId || !$orId || !$leadSurgeonId || empty($procedureName) || empty($scheduledStart) || empty($scheduledEnd)) {
            Session::flash('error', 'يرجى ملء جميع الحقول الإلزامية لحجز العملية الجراحية.');
            $this->redirect(url('/surgery/create'));
            return;
        }

        if (strtotime($scheduledEnd) <= strtotime($scheduledStart)) {
            Session::flash('error', 'وقت نهاية العملية المجدول يجب أن يكون بعد وقت البداية.');
            $this->redirect(url('/surgery/create'));
            return;
        }

        // Conflict check for Operating Room
        if ($this->surgeryModel->checkOrConflict($orId, $scheduledStart, $scheduledEnd)) {
            Session::flash('error', 'يوجد تعارض في جدول غرفة العمليات المختارة مع عملية جراحية أخرى في نفس التوقيت!');
            $this->redirect(url('/surgery/create'));
            return;
        }

        // Generate number
        $surgeryNumber = $this->surgeryModel->generateSurgeryNumber();

        $data = [
            'surgery_number' => $surgeryNumber,
            'patient_id' => $patientId,
            'admission_id' => $request->input('admission_id') ? (int)$request->input('admission_id') : null,
            'operating_room_id' => $orId,
            'lead_surgeon_id' => $leadSurgeonId,
            'assistant_surgeon_id' => $request->input('assistant_surgeon_id') ? (int)$request->input('assistant_surgeon_id') : null,
            'anesthesiologist_id' => $request->input('anesthesiologist_id') ? (int)$request->input('anesthesiologist_id') : null,
            'scrub_nurse_id' => $request->input('scrub_nurse_id') ? (int)$request->input('scrub_nurse_id') : null,
            'surgery_type' => $surgeryType,
            'procedure_name' => $procedureName,
            'scheduled_start' => $scheduledStart,
            'scheduled_end' => $scheduledEnd,
            'status' => 'scheduled',
            'pre_op_diagnosis' => $request->input('pre_op_diagnosis'),
            'created_by' => auth()->id(),
        ];

        $surgeryId = $this->surgeryModel->create($data);

        AuditLog::log('surgeries', $surgeryId, 'CREATE', null, $data);

        Session::flash('success', "تمت جدولة العملية الجراحية بنجاح برقم {$surgeryNumber}.");
        $this->redirect(url('/surgery/' . $surgeryId));
    }

    public function show(Request $request, Response $response, ?string $id = null): string
    {
        $id = (int)($id ?: $request->query('id'));
        $surgery = $this->surgeryModel->detailed($id);

        if (!$surgery) {
            Session::flash('error', 'العملية الجراحية المطلوبة غير موجودة.');
            $this->redirect(url('/surgery'));
            return '';
        }

        $anesthesia = $this->anesthesiaModel->detailedForSurgery($id);

        $anesthesiologists = $this->db->fetchAll("SELECT d.id, CONCAT(e.first_name, ' ', e.last_name) AS name
            FROM doctors d
            JOIN employees e ON d.employee_id = e.id
            JOIN specialties s ON d.specialty_id = s.id
            WHERE s.code = 'SPEC-ANES' OR s.name_en LIKE '%Anesthesia%'");
        if (empty($anesthesiologists)) {
            $anesthesiologists = $this->db->fetchAll("SELECT d.id, CONCAT(e.first_name, ' ', e.last_name) AS name FROM doctors d JOIN employees e ON d.employee_id = e.id");
        }

        return $this->view('surgery/show', [
            'surgery' => $surgery,
            'anesthesia' => $anesthesia,
            'anesthesiologists' => $anesthesiologists,
        ]);
    }

    public function updateStatus(Request $request, Response $response, ?string $id = null): void
    {
        $id = (int)($id ?: $request->input('id'));
        $status = $request->input('status');
        $surgery = $this->surgeryModel->find($id);

        if (!$surgery) {
            Session::flash('error', 'العملية الجراحية غير موجودة.');
            $this->redirect(url('/surgery'));
            return;
        }

        $updates = ['status' => $status];

        if ($status === 'in_progress') {
            $updates['actual_start'] = date('Y-m-d H:i:s');
            // Update Operating Room status
            $this->db->query("UPDATE operating_rooms SET status = 'in_surgery' WHERE id = :or_id", [
                'or_id' => $surgery['operating_room_id']
            ]);
        } elseif ($status === 'completed') {
            $updates['actual_end'] = date('Y-m-d H:i:s');
            $updates['post_op_diagnosis'] = $request->input('post_op_diagnosis', $surgery['post_op_diagnosis']);
            $updates['surgery_notes'] = $request->input('surgery_notes', $surgery['surgery_notes']);
            // Update Operating Room status to cleaning
            $this->db->query("UPDATE operating_rooms SET status = 'cleaning' WHERE id = :or_id", [
                'or_id' => $surgery['operating_room_id']
            ]);
        } elseif (in_array($status, ['postponed', 'cancelled'])) {
            // Free operating room if it was reserved
            $this->db->query("UPDATE operating_rooms SET status = 'available' WHERE id = :or_id", [
                'or_id' => $surgery['operating_room_id']
            ]);
        }

        $this->surgeryModel->update($id, $updates);

        AuditLog::log('surgeries', $id, 'UPDATE_STATUS', $surgery, $updates);

        Session::flash('success', 'تم تحديث حالة العملية الجراحية بنجاح.');
        $this->redirect(url('/surgery/' . $id));
    }

    public function saveAnesthesia(Request $request, Response $response): void
    {
        $surgeryId = (int)$request->input('surgery_id');
        $anesthesiologistId = (int)$request->input('anesthesiologist_id');
        $anesthesiaType = $request->input('anesthesia_type', 'general');
        $asaScore = $request->input('asa_physical_status', 'I');

        if (!$surgeryId || !$anesthesiologistId) {
            Session::flash('error', 'يرجى تحديد العملية وطبيب التخدير.');
            $this->redirect(url('/surgery/' . $surgeryId));
            return;
        }

        $existing = $this->db->fetchOne("SELECT id FROM anesthesia_records WHERE surgery_id = :sid", ['sid' => $surgeryId]);

        $data = [
            'surgery_id' => $surgeryId,
            'anesthesiologist_id' => $anesthesiologistId,
            'anesthesia_type' => $anesthesiaType,
            'asa_physical_status' => $asaScore,
            'pre_anesthesia_assessment' => $request->input('pre_anesthesia_assessment'),
            'airway_assessment' => $request->input('airway_assessment'),
            'start_time' => $request->input('start_time') ?: null,
            'end_time' => $request->input('end_time') ?: null,
            'agents_used' => $request->input('agents_used'),
            'recovery_score' => $request->input('recovery_score'),
            'complications' => $request->input('complications'),
            'anesthesia_notes' => $request->input('anesthesia_notes'),
        ];

        if ($existing) {
            $this->anesthesiaModel->update((int)$existing['id'], $data);
            AuditLog::log('anesthesia_records', (int)$existing['id'], 'UPDATE', null, $data);
        } else {
            $id = $this->anesthesiaModel->create($data);
            AuditLog::log('anesthesia_records', $id, 'CREATE', null, $data);
        }

        // Also update surgery's anesthesiologist_id if not set
        $this->db->query("UPDATE surgeries SET anesthesiologist_id = :aid WHERE id = :sid AND anesthesiologist_id IS NULL", [
            'aid' => $anesthesiologistId,
            'sid' => $surgeryId
        ]);

        Session::flash('success', 'تم حفظ وتوثيق سجل التخدير الطبي بنجاح.');
        $this->redirect(url('/surgery/' . $surgeryId));
    }

    public function printReport(Request $request, Response $response, ?string $id = null): string
    {
        $id = (int)($id ?: $request->query('id'));
        $surgery = $this->surgeryModel->detailed($id);

        if (!$surgery) {
            Session::flash('error', 'العملية الجراحية غير موجودة.');
            $this->redirect(url('/surgery'));
            return '';
        }

        $anesthesia = $this->anesthesiaModel->detailedForSurgery($id);

        return $this->view('surgery/print_report', [
            'surgery' => $surgery,
            'anesthesia' => $anesthesia,
        ], null);
    }
}
