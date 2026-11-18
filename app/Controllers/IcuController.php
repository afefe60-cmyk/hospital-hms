<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\IcuStay;
use App\Models\IcuObservation;
use App\Models\Patient;
use App\Models\AuditLog;

class IcuController extends Controller
{
    private IcuStay $icuModel;
    private IcuObservation $observationModel;
    private Database $db;

    public function __construct()
    {
        $this->icuModel = new IcuStay();
        $this->observationModel = new IcuObservation();
        $this->db = Database::getInstance();
    }

    public function index(Request $request, Response $response): string
    {
        $status = $request->query('status', 'admitted');

        $stays = $this->icuModel->listWithDetails($status === 'all' ? null : $status);

        // Fetch all ICU Beds with current occupancy
        $icuBeds = $this->db->fetchAll("SELECT b.*, r.room_number, w.name_ar AS ward_name_ar,
            s.id AS active_stay_id, s.stay_number, s.ventilator_status, s.admitted_at,
            CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS current_patient_name,
            p.mrn AS current_patient_mrn
            FROM beds b
            INNER JOIN rooms r ON b.room_id = r.id
            INNER JOIN wards w ON r.ward_id = w.id
            LEFT JOIN icu_stays s ON s.bed_id = b.id AND s.status = 'admitted'
            LEFT JOIN patients p ON s.patient_id = p.id
            WHERE r.room_type = 'isolation' OR w.name_en LIKE '%ICU%' OR w.name_ar LIKE '%العناية%' OR b.bed_number LIKE 'ICU%'
            ORDER BY b.bed_number ASC");

        // Metrics
        $totalBeds = count($icuBeds);
        $occupiedBeds = count(array_filter($icuBeds, fn($b) => $b['status'] === 'occupied' || !empty($b['active_stay_id'])));
        $availableBeds = $totalBeds - $occupiedBeds;
        $ventilatedCount = count(array_filter($icuBeds, fn($b) => in_array($b['ventilator_status'], ['invasive', 'non_invasive'])));

        return $this->view('icu/index', [
            'stays' => $stays,
            'icuBeds' => $icuBeds,
            'stats' => [
                'total_beds' => $totalBeds,
                'occupied_beds' => $occupiedBeds,
                'available_beds' => $availableBeds,
                'ventilated_count' => $ventilatedCount,
            ],
            'selectedStatus' => $status,
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

        // Available ICU Beds
        $beds = $this->db->fetchAll("SELECT b.id, b.bed_number, r.room_number, w.name_ar AS ward_name
            FROM beds b
            INNER JOIN rooms r ON b.room_id = r.id
            INNER JOIN wards w ON r.ward_id = w.id
            WHERE (r.room_type = 'isolation' OR w.name_en LIKE '%ICU%' OR w.name_ar LIKE '%العناية%' OR b.bed_number LIKE 'ICU%')
            AND b.status = 'available'
            ORDER BY b.bed_number ASC");

        // Intensivists / Doctors
        $intensivists = $this->db->fetchAll("SELECT d.id, CONCAT(e.first_name, ' ', e.last_name) AS name, s.name_ar AS specialty_name
            FROM doctors d
            JOIN employees e ON d.employee_id = e.id
            JOIN specialties s ON d.specialty_id = s.id
            ORDER BY e.first_name ASC");

        $patients = $this->db->fetchAll("SELECT id, mrn, CONCAT(first_name, ' ', second_name, ' ', last_name) AS name, phone_primary FROM patients ORDER BY id DESC LIMIT 50");

        return $this->view('icu/create', [
            'patient' => $patient,
            'patients' => $patients,
            'beds' => $beds,
            'intensivists' => $intensivists,
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $patientId = (int)$request->input('patient_id');
        $bedId = (int)$request->input('bed_id');
        $reason = trim($request->input('admission_reason', ''));
        $intensivistId = $request->input('attending_intensivist_id') ? (int)$request->input('attending_intensivist_id') : null;
        $ventStatus = $request->input('ventilator_status', 'none');
        $apacheScore = $request->input('apache_score') !== '' ? (int)$request->input('apache_score') : null;

        if (!$patientId || !$bedId || empty($reason)) {
            Session::flash('error', 'يرجى اختيار المريض، سرير العناية وتدوين سبب الدخول.');
            $this->redirect(url('/icu/create'));
            return;
        }

        // Check if bed is available
        $bed = $this->db->fetchOne("SELECT status FROM beds WHERE id = :bid", ['bid' => $bedId]);
        if (!$bed || $bed['status'] === 'occupied') {
            Session::flash('error', 'سرير العناية المركزة المحدد مشغول حالياً. يرجى اختيار سرير شاغر.');
            $this->redirect(url('/icu/create'));
            return;
        }

        $stayNumber = $this->icuModel->generateStayNumber();

        $data = [
            'stay_number' => $stayNumber,
            'patient_id' => $patientId,
            'admission_id' => $request->input('admission_id') ? (int)$request->input('admission_id') : null,
            'bed_id' => $bedId,
            'attending_intensivist_id' => $intensivistId,
            'admitted_at' => date('Y-m-d H:i:s'),
            'admission_reason' => $reason,
            'apache_score' => $apacheScore,
            'ventilator_status' => $ventStatus,
            'status' => 'admitted',
        ];

        $stayId = $this->icuModel->create($data);

        // Update bed status to occupied
        $this->db->query("UPDATE beds SET status = 'occupied' WHERE id = :bid", ['bid' => $bedId]);

        AuditLog::log('icu_stays', $stayId, 'CREATE', null, $data);

        Session::flash('success', "تم إدخال المريض لوحدة العناية المركزة بنجاح برقم إقامة {$stayNumber}.");
        $this->redirect(url('/icu/' . $stayId));
    }

    public function show(Request $request, Response $response, ?string $id = null): string
    {
        $id = (int)($id ?: $request->query('id'));
        $stay = $this->icuModel->detailed($id);

        if (!$stay) {
            Session::flash('error', 'إقامة العناية المركزة غير موجودة.');
            $this->redirect(url('/icu'));
            return '';
        }

        $observations = $this->observationModel->forStay($id, 50);

        // Calculate Fluid Balance
        $totalFluidsIn = 0;
        $totalFluidsOut = 0;
        foreach ($observations as $obs) {
            $totalFluidsIn += (int)($obs['iv_fluids_ml'] ?? 0);
            $totalFluidsOut += (int)($obs['urine_output_ml'] ?? 0);
        }
        $netFluidBalance = $totalFluidsIn - $totalFluidsOut;

        // Latest vitals for summary strip
        $latest = !empty($observations) ? $observations[0] : null;

        return $this->view('icu/show', [
            'stay' => $stay,
            'observations' => $observations,
            'fluidBalance' => [
                'total_in' => $totalFluidsIn,
                'total_out' => $totalFluidsOut,
                'net' => $netFluidBalance,
            ],
            'latest' => $latest,
        ]);
    }

    public function recordObservation(Request $request, Response $response): void
    {
        $stayId = (int)$request->input('icu_stay_id');
        $stay = $this->icuModel->find($stayId);

        if (!$stay) {
            Session::flash('error', 'سجل إقامة العناية غير موجود.');
            $this->redirect(url('/icu'));
            return;
        }

        $recordedAt = $request->input('recorded_at') ?: date('Y-m-d H:i:s');
        $gcs = $request->input('gcs_score') !== '' ? (int)$request->input('gcs_score') : null;

        $obsData = [
            'icu_stay_id' => $stayId,
            'recorded_at' => $recordedAt,
            'recorded_by' => auth()->id(),
            'systolic_bp' => $request->input('systolic_bp') !== '' ? (int)$request->input('systolic_bp') : null,
            'diastolic_bp' => $request->input('diastolic_bp') !== '' ? (int)$request->input('diastolic_bp') : null,
            'pulse_rate' => $request->input('pulse_rate') !== '' ? (int)$request->input('pulse_rate') : null,
            'respiratory_rate' => $request->input('respiratory_rate') !== '' ? (int)$request->input('respiratory_rate') : null,
            'temperature' => $request->input('temperature') !== '' ? (float)$request->input('temperature') : null,
            'oxygen_saturation' => $request->input('oxygen_saturation') !== '' ? (float)$request->input('oxygen_saturation') : null,
            'gcs_score' => $gcs,
            'pupil_reaction' => $request->input('pupil_reaction', 'brisk'),
            'ventilator_mode' => $request->input('ventilator_mode'),
            'fio2' => $request->input('fio2') !== '' ? (int)$request->input('fio2') : null,
            'peep' => $request->input('peep') !== '' ? (int)$request->input('peep') : null,
            'urine_output_ml' => $request->input('urine_output_ml') !== '' ? (int)$request->input('urine_output_ml') : null,
            'iv_fluids_ml' => $request->input('iv_fluids_ml') !== '' ? (int)$request->input('iv_fluids_ml') : null,
            'arterial_blood_gas' => $request->input('arterial_blood_gas'),
            'clinical_notes' => $request->input('clinical_notes'),
        ];

        $obsId = $this->observationModel->create($obsData);

        // Auto update ventilator status on icu_stay if changed
        $ventMode = $request->input('ventilator_mode');
        if (!empty($ventMode) && $ventMode !== 'Off' && $ventMode !== 'None') {
            if ($stay['ventilator_status'] === 'none') {
                $this->icuModel->update($stayId, ['ventilator_status' => 'invasive']);
            }
        }

        AuditLog::log('icu_observations', $obsId, 'CREATE', null, $obsData);

        Session::flash('success', 'تم تدوين الملاحظة والعلامات الحيوية للعناية المركزة بنجاح.');
        $this->redirect(url('/icu/' . $stayId));
    }

    public function updateStatus(Request $request, Response $response): void
    {
        $id = (int)$request->input('id');
        $status = $request->input('status');
        $summary = $request->input('discharge_summary');

        $stay = $this->icuModel->find($id);
        if (!$stay) {
            Session::flash('error', 'سجل إقامة العناية غير موجود.');
            $this->redirect(url('/icu'));
            return;
        }

        $updates = [
            'status' => $status,
            'discharged_at' => date('Y-m-d H:i:s'),
            'discharge_summary' => $summary,
        ];

        $this->icuModel->update($id, $updates);

        // Free the ICU bed (set to cleaning)
        $this->db->query("UPDATE beds SET status = 'cleaning' WHERE id = :bid", ['bid' => $stay['bed_id']]);

        AuditLog::log('icu_stays', $id, 'UPDATE_STATUS', $stay, $updates);

        Session::flash('success', 'تم إنهاء إقامة المريض بالعناية وتحديث حالة السرير إلى التعقيم بنجاح.');
        $this->redirect(url('/icu/' . $id));
    }

    public function printChart(Request $request, Response $response, ?string $id = null): string
    {
        $id = (int)($id ?: $request->query('id'));
        $stay = $this->icuModel->detailed($id);

        if (!$stay) {
            Session::flash('error', 'إقامة العناية المركزة غير موجودة.');
            $this->redirect(url('/icu'));
            return '';
        }

        $observations = $this->observationModel->forStay($id, 24);

        return $this->view('icu/print_chart', [
            'stay' => $stay,
            'observations' => $observations,
        ], null);
    }
}
