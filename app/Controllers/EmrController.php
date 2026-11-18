<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Clinic;
use App\Models\Diagnosis;
use App\Models\DiagnosisCatalog;
use App\Models\Doctor;
use App\Models\Encounter;
use App\Models\MedicalOrder;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Queue;
use App\Models\Visit;
use App\Models\VitalSign;

class EmrController extends Controller
{
    private Encounter $encounterModel;
    private VitalSign $vitalModel;
    private Diagnosis $diagnosisModel;
    private DiagnosisCatalog $catalogModel;
    private Prescription $prescriptionModel;
    private PrescriptionItem $prescriptionItemModel;
    private MedicalOrder $orderModel;
    private Visit $visitModel;
    private Patient $patientModel;
    private Queue $queueModel;
    private Doctor $doctorModel;

    public function __construct()
    {
        $this->encounterModel = new Encounter();
        $this->vitalModel = new VitalSign();
        $this->diagnosisModel = new Diagnosis();
        $this->catalogModel = new DiagnosisCatalog();
        $this->prescriptionModel = new Prescription();
        $this->prescriptionItemModel = new PrescriptionItem();
        $this->orderModel = new MedicalOrder();
        $this->visitModel = new Visit();
        $this->patientModel = new Patient();
        $this->queueModel = new Queue();
        $this->doctorModel = new Doctor();
    }

    public function dashboard(Request $request, Response $response): string
    {
        // Get active visits waiting for doctor review
        $db = \App\Core\Database::getInstance();
        $today = date('Y-m-d');

        $sql = "SELECT v.*,
                       p.mrn, p.gender, p.date_of_birth, p.blood_group,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                       d.name_ar AS department_name,
                       q.id AS queue_id, q.queue_number, q.status AS queue_status,
                       enc.id AS active_encounter_id
                FROM `visits` v
                INNER JOIN `patients` p ON v.patient_id = p.id
                INNER JOIN `departments` d ON v.department_id = d.id
                LEFT JOIN `queues` q ON v.id = q.visit_id AND DATE(q.created_at) = :today
                LEFT JOIN `encounters` enc ON v.id = enc.visit_id AND enc.end_time IS NULL
                WHERE v.status = 'active'
                ORDER BY q.queue_number ASC, v.id ASC";
        $waitingPatients = $db->fetchAll($sql, ['today' => $today]);

        return $this->view('emr/dashboard', [
            'pageTitle' => 'لوحة الطبيب السريرية وإدارة الكشف (Doctor EMR Station)',
            'waitingPatients' => $waitingPatients,
        ]);
    }

    public function startEncounter(Request $request, Response $response, string $visitId): void
    {
        $visit = $this->visitModel->find($visitId);
        if (!$visit) {
            Session::flash('error', 'الزيارة غير موجودة.');
            $this->redirect(url('/emr'));
            return;
        }

        // Check if an encounter already exists for this visit
        $existing = $this->encounterModel->firstWhere('visit_id', $visitId);
        if ($existing) {
            $this->redirect(url("/emr/encounter/{$existing['id']}"));
            return;
        }

        // Resolve doctor id
        $doctorId = $visit['attending_doctor_id'];
        if (!$doctorId) {
            $firstDoc = $this->doctorModel->all();
            $doctorId = !empty($firstDoc) ? $firstDoc[0]['id'] : 1;
        }

        $encounterId = $this->encounterModel->create([
            'visit_id' => $visitId,
            'doctor_id' => $doctorId,
            'encounter_type' => 'initial',
            'chief_complaint' => 'معاينة سريرية أولية',
            'start_time' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Update queue status to in_room
        $db = \App\Core\Database::getInstance();
        $db->query("UPDATE `queues` SET `status` = 'in_room' WHERE `visit_id` = :vid", ['vid' => $visitId]);

        AuditLog::log('START_ENCOUNTER', 'encounters', $encounterId, null, ['visit_id' => $visitId]);

        Session::flash('success', 'تم فتح جلسة الكشف السريري للمريض.');
        $this->redirect(url("/emr/encounter/{$encounterId}"));
    }

    public function encounter(Request $request, Response $response, string $id): string
    {
        $encounter = $this->encounterModel->detailed((int)$id);
        if (!$encounter) {
            Session::flash('error', 'جلسة الكشف غير موجودة.');
            $this->redirect(url('/emr'));
        }

        $vitals = $this->encounterModel->vitals((int)$id);
        $diagnoses = $this->encounterModel->diagnoses((int)$id);
        $prescriptions = $this->encounterModel->prescriptions((int)$id);
        $orders = $this->encounterModel->orders((int)$id);

        $patientAllergies = $this->patientModel->allergies((int)$encounter['patient_id']);
        $patientConditions = $this->patientModel->conditions((int)$encounter['patient_id']);
        $icd10Catalog = $this->catalogModel->all('icd10_code', 'ASC');

        return $this->view('emr/encounter', [
            'pageTitle' => "الكشف السريري: {$encounter['patient_name']} ({$encounter['mrn']})",
            'encounter' => $encounter,
            'vitals' => $vitals,
            'diagnoses' => $diagnoses,
            'prescriptions' => $prescriptions,
            'orders' => $orders,
            'allergies' => $patientAllergies,
            'conditions' => $patientConditions,
            'icd10Catalog' => $icd10Catalog,
        ]);
    }

    public function saveVitals(Request $request, Response $response, string $id): void
    {
        $encounter = $this->encounterModel->find($id);
        if (!$encounter) {
            Session::flash('error', 'جلسة الكشف غير موجودة.');
            $this->redirect(url('/emr'));
            return;
        }

        $weight = $request->input('weight_kg') ? (float)$request->input('weight_kg') : null;
        $height = $request->input('height_cm') ? (float)$request->input('height_cm') : null;
        $bmi = VitalSign::calculateBmi($weight, $height);

        $this->vitalModel->create([
            'encounter_id' => (int)$id,
            'visit_id' => $encounter['visit_id'],
            'systolic_bp' => $request->input('systolic_bp') ? (int)$request->input('systolic_bp') : null,
            'diastolic_bp' => $request->input('diastolic_bp') ? (int)$request->input('diastolic_bp') : null,
            'pulse_rate' => $request->input('pulse_rate') ? (int)$request->input('pulse_rate') : null,
            'respiratory_rate' => $request->input('respiratory_rate') ? (int)$request->input('respiratory_rate') : null,
            'temperature' => $request->input('temperature') ? (float)$request->input('temperature') : null,
            'oxygen_saturation' => $request->input('oxygen_saturation') ? (int)$request->input('oxygen_saturation') : null,
            'random_blood_sugar' => $request->input('random_blood_sugar') ? (int)$request->input('random_blood_sugar') : null,
            'weight_kg' => $weight,
            'height_cm' => $height,
            'bmi' => $bmi,
            'recorded_by' => auth()->id(),
            'recorded_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLog::log('RECORD_VITALS', 'vital_signs', $id, null, ['bmi' => $bmi]);

        Session::flash('success', 'تم حفظ العلامات الحيوية وحساب مؤشر كتلة الجسم (BMI) بنجاح.');
        $this->redirect(url("/emr/encounter/{$id}"));
    }

    public function saveNotes(Request $request, Response $response, string $id): void
    {
        $encounter = $this->encounterModel->find($id);
        if (!$encounter) {
            Session::flash('error', 'جلسة الكشف غير موجودة.');
            $this->redirect(url('/emr'));
            return;
        }

        $this->encounterModel->update($id, [
            'chief_complaint' => trim((string)$request->input('chief_complaint')),
            'history_present_illness' => trim((string)$request->input('history_present_illness')),
            'physical_examination' => trim((string)$request->input('physical_examination')),
            'clinical_plan' => trim((string)$request->input('clinical_plan')),
        ]);

        AuditLog::log('UPDATE_CLINICAL_NOTES', 'encounters', $id);

        Session::flash('success', 'تم تحديث الملاحظات الطبية وخطة العلاج.');
        $this->redirect(url("/emr/encounter/{$id}"));
    }

    public function addDiagnosis(Request $request, Response $response, string $id): void
    {
        $encounter = $this->encounterModel->find($id);
        if (!$encounter) {
            Session::flash('error', 'جلسة الكشف غير موجودة.');
            $this->redirect(url('/emr'));
            return;
        }

        $catalogId = (int)$request->input('diagnosis_catalog_id');
        if ($catalogId <= 0) {
            Session::flash('error', 'يرجى اختيار التشخيص الطبي المعتمد.');
            $this->redirect(url("/emr/encounter/{$id}"));
            return;
        }

        $this->diagnosisModel->create([
            'encounter_id' => (int)$id,
            'diagnosis_catalog_id' => $catalogId,
            'type' => $request->input('type', 'primary'),
            'clinical_notes' => trim((string)$request->input('clinical_notes')),
            'diagnosed_by' => $encounter['doctor_id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLog::log('ADD_DIAGNOSIS', 'diagnoses', $id, null, ['catalog_id' => $catalogId]);

        Session::flash('success', 'تم تسجيل التشخيص الطبي المعتمد وفق التصنيف الدولي (ICD-10).');
        $this->redirect(url("/emr/encounter/{$id}"));
    }

    public function addPrescription(Request $request, Response $response, string $id): void
    {
        $encounter = $this->encounterModel->detailed((int)$id);
        if (!$encounter) {
            Session::flash('error', 'جلسة الكشف غير موجودة.');
            $this->redirect(url('/emr'));
            return;
        }

        $medicineNames = $request->input('medicine_name', []);
        $dosages = $request->input('dosage', []);
        $frequencies = $request->input('frequency', []);
        $durations = $request->input('duration_days', []);
        $quantities = $request->input('total_quantity', []);
        $instructions = $request->input('instructions', []);

        if (empty($medicineNames) || !is_array($medicineNames)) {
            Session::flash('error', 'يرجى إدخال صنف دوائي واحد على الأقل.');
            $this->redirect(url("/emr/encounter/{$id}"));
            return;
        }

        // CLINICAL SAFETY: Allergy Cross-Check
        $allergies = $this->patientModel->allergies((int)$encounter['patient_id']);

        foreach ($medicineNames as $med) {
            $lowerMed = strtolower(trim($med));
            if (empty($lowerMed)) continue;

            foreach ($allergies as $algRecord) {
                $rawAlg = strtolower($algRecord['allergen_name']);
                // Tokenize allergen name by spaces, brackets, slashes
                $tokens = preg_split('/[\s\(\)\[\]\/\-\,\.]+/u', $rawAlg, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($tokens as $token) {
                    $token = trim($token);
                    if (mb_strlen($token) >= 3 && (str_contains($lowerMed, $token) || str_contains($rawAlg, $lowerMed))) {
                        Session::flash('error', "تحذير سريري حرج: المريض لديه حساسية مسجلة من ({$algRecord['allergen_name']})! تم حظر صرف الدواء لحماية حياة المريض.");
                        $this->redirect(url("/emr/encounter/{$id}"));
                        return;
                    }
                }
            }
        }

        $prescriptionCode = $this->prescriptionModel->generatePrescriptionCode();
        $prescriptionId = $this->prescriptionModel->create([
            'prescription_code' => $prescriptionCode,
            'encounter_id' => (int)$id,
            'patient_id' => $encounter['patient_id'],
            'doctor_id' => $encounter['doctor_id'],
            'status' => 'pending',
            'notes' => trim((string)$request->input('notes')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        for ($i = 0; $i < count($medicineNames); $i++) {
            $medName = trim((string)($medicineNames[$i] ?? ''));
            if (!empty($medName)) {
                $this->prescriptionItemModel->create([
                    'prescription_id' => $prescriptionId,
                    'medicine_name' => $medName,
                    'dosage' => trim((string)($dosages[$i] ?? 'قرص واحد')),
                    'frequency' => trim((string)($frequencies[$i] ?? 'مرتين يومياً')),
                    'duration_days' => max(1, (int)($durations[$i] ?? 5)),
                    'total_quantity' => max(1, (int)($quantities[$i] ?? 1)),
                    'instructions' => trim((string)($instructions[$i] ?? '')),
                ]);
            }
        }

        AuditLog::log('CREATE_PRESCRIPTION', 'prescriptions', $prescriptionId, null, [
            'code' => $prescriptionCode,
            'items_count' => count($medicineNames),
        ]);

        Session::flash('success', "تم تحرير الروشتة الطبية الإلكترونية بنجاح ({$prescriptionCode}).");
        $this->redirect(url("/emr/encounter/{$id}"));
    }

    public function addOrder(Request $request, Response $response, string $id): void
    {
        $encounter = $this->encounterModel->find($id);
        if (!$encounter) {
            Session::flash('error', 'جلسة الكشف غير موجودة.');
            $this->redirect(url('/emr'));
            return;
        }

        $orderType = $request->input('order_type');
        $description = trim((string)$request->input('order_description'));

        if (empty($description)) {
            Session::flash('error', 'يرجى إدخال وصف الفحص المطلوب.');
            $this->redirect(url("/emr/encounter/{$id}"));
            return;
        }

        $this->orderModel->create([
            'encounter_id' => (int)$id,
            'visit_id' => $encounter['visit_id'],
            'order_type' => $orderType,
            'order_description' => $description,
            'priority' => $request->input('priority', 'routine'),
            'ordering_doctor_id' => $encounter['doctor_id'],
            'status' => 'ordered',
            'clinical_indication' => trim((string)$request->input('clinical_indication')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLog::log('CREATE_MEDICAL_ORDER', 'medical_orders', $id, null, [
            'type' => $orderType,
            'description' => $description,
        ]);

        Session::flash('success', 'تم إصدار الطلب الطبي وإرساله للقسم المختص بنجاح.');
        $this->redirect(url("/emr/encounter/{$id}"));
    }

    public function completeEncounter(Request $request, Response $response, string $id): void
    {
        $encounter = $this->encounterModel->find($id);
        if ($encounter) {
            $this->encounterModel->update($id, ['end_time' => date('Y-m-d H:i:s')]);
            $this->visitModel->update($encounter['visit_id'], ['status' => 'discharged', 'discharge_time' => date('Y-m-d H:i:s')]);

            // Update queue to completed
            $db = \App\Core\Database::getInstance();
            $db->query("UPDATE `queues` SET `status` = 'completed' WHERE `visit_id` = :vid", ['vid' => $encounter['visit_id']]);

            AuditLog::log('COMPLETE_ENCOUNTER', 'encounters', $id);
            Session::flash('success', 'تم إنهاء جلسة الكشف الطبي وإغلاق الزيارة بنجاح.');
        }

        $this->redirect(url('/emr'));
    }

    public function printPrescription(Request $request, Response $response, string $prescriptionId): string
    {
        $prescription = $this->prescriptionModel->detailed((int)$prescriptionId);
        if (!$prescription) {
            $response->redirect(url('/emr'));
        }

        $items = $this->prescriptionModel->items((int)$prescriptionId);

        return $this->view('emr/print_prescription', [
            'pageTitle' => "روشتة طبية - {$prescription['prescription_code']}",
            'prescription' => $prescription,
            'items' => $items,
        ], null); // Render without layout for printing
    }
}
