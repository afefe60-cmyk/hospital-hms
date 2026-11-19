<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\PatientCondition;

class PatientController extends Controller
{
    private Patient $patientModel;
    private PatientAllergy $allergyModel;
    private PatientCondition $conditionModel;

    public function __construct()
    {
        $this->patientModel = new Patient();
        $this->allergyModel = new PatientAllergy();
        $this->conditionModel = new PatientCondition();
    }

    public function index(Request $request, Response $response): string
    {
        if (!can('patients.view')) {
            Session::flash('error', 'ليس لديك صلاحية عرض سجلات المرضى.');
            $this->redirect(url('/'));
        }

        $page = (int)$request->query('page', 1);
        $query = trim((string)$request->query('q', ''));

        $results = $this->patientModel->search($query, $page, 15);

        return $this->view('patients/index', [
            'pageTitle' => 'سجل المرضى الموحد',
            'patients' => $results['data'],
            'searchQuery' => $query,
            'currentPage' => $results['current_page'],
            'totalPages' => $results['last_page'],
            'totalRecords' => $results['total'],
        ]);
    }

    public function create(Request $request, Response $response): string
    {
        if (!can('patients.create')) {
            Session::flash('error', 'ليس لديك صلاحية فتح ملف مريض جديد.');
            $this->redirect(url('/patients'));
        }

        $nextMrn = $this->patientModel->generateNextMrn();

        return $this->view('patients/create', [
            'pageTitle' => 'فتح ملف طبي جديد (MRN)',
            'nextMrn' => $nextMrn,
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        if (!can('patients.create')) {
            Session::flash('error', 'غير مصرح.');
            $this->redirect(url('/patients'));
            return;
        }

        $validation = $this->validate($request->all(), [
            'first_name' => 'required',
            'second_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'date_of_birth' => 'required',
            'phone_primary' => 'required',
            'address_state' => 'required',
            'address_city' => 'required',
            'emergency_contact_name' => 'required',
            'emergency_contact_phone' => 'required',
        ]);

        if (!$validation['isValid']) {
            $this->redirect(url('/patients/create'));
            return;
        }

        $nationalId = trim((string)$request->input('national_id'));
        if (!empty($nationalId)) {
            $existing = $this->patientModel->firstWhere('national_id', $nationalId);
            if ($existing) {
                Session::flash('error', "يوجد مريض مسجل مسبقاً بنفس الرقم الوطني: {$existing['first_name']} {$existing['last_name']} (MRN: {$existing['mrn']}).");
                Session::flash('_old_input', $request->all());
                $this->redirect(url('/patients/create'));
                return;
            }
        }

        $mrn = $this->patientModel->generateNextMrn();

        $patientId = $this->patientModel->create([
            'mrn' => $mrn,
            'national_id' => !empty($nationalId) ? $nationalId : null,
            'first_name' => trim((string)$request->input('first_name')),
            'second_name' => trim((string)$request->input('second_name')),
            'third_name' => trim((string)$request->input('third_name')),
            'last_name' => trim((string)$request->input('last_name')),
            'gender' => $request->input('gender'),
            'date_of_birth' => $request->input('date_of_birth'),
            'blood_group' => $request->input('blood_group', 'unknown'),
            'marital_status' => $request->input('marital_status'),
            'phone_primary' => trim((string)$request->input('phone_primary')),
            'phone_secondary' => trim((string)$request->input('phone_secondary')),
            'address_state' => trim((string)$request->input('address_state')),
            'address_city' => trim((string)$request->input('address_city')),
            'emergency_contact_name' => trim((string)$request->input('emergency_contact_name')),
            'emergency_contact_phone' => trim((string)$request->input('emergency_contact_phone')),
            'emergency_contact_relation' => trim((string)$request->input('emergency_contact_relation')),
            'created_by' => auth()->id(),
        ]);

        // If initial allergy specified
        $initialAllergy = trim((string)$request->input('initial_allergy'));
        if (!empty($initialAllergy)) {
            $this->allergyModel->create([
                'patient_id' => $patientId,
                'allergen_type' => $request->input('allergy_type', 'drug'),
                'allergen_name' => $initialAllergy,
                'severity' => $request->input('allergy_severity', 'severe'),
                'reaction_notes' => 'تم التسجيل أثناء فتح الملف الأولي',
                'recorded_by' => auth()->id(),
            ]);
        }

        AuditLog::log('CREATE_PATIENT', 'patients', $patientId, null, [
            'mrn' => $mrn,
            'name' => $request->input('first_name') . ' ' . $request->input('last_name'),
            'phone' => $request->input('phone_primary'),
        ]);

        Session::flash('success', "تم فتح الملف الطبي بنجاح بالرقم الطبي الموحد: {$mrn}");
        $this->redirect(url("/patients/{$patientId}"));
    }

    public function show(Request $request, Response $response, string $id): string
    {
        if (!can('patients.view')) {
            Session::flash('error', 'غير مصرح.');
            $this->redirect(url('/patients'));
        }

        $patient = $this->patientModel->find($id);
        if (!$patient) {
            Session::flash('error', 'ملف المريض المطلوب غير موجود.');
            $this->redirect(url('/patients'));
        }

        $allergies = $this->patientModel->allergies((int)$id);
        $conditions = $this->patientModel->conditions((int)$id);
        $appointments = $this->patientModel->appointments((int)$id);
        $visits = $this->patientModel->visits((int)$id);

        // Audit view of sensitive clinical record
        AuditLog::log('VIEW_PATIENT_FILE', 'patients', $id);

        return $this->view('patients/show', [
            'pageTitle' => "ملف المريض: {$patient['first_name']} {$patient['last_name']} ({$patient['mrn']})",
            'patient' => $patient,
            'allergies' => $allergies,
            'conditions' => $conditions,
            'appointments' => $appointments,
            'visits' => $visits,
        ]);
    }

    public function addAllergy(Request $request, Response $response, string $id): void
    {
        $patient = $this->patientModel->find($id);
        if (!$patient) {
            Session::flash('error', 'المريض غير موجود.');
            $this->redirect(url('/patients'));
            return;
        }

        $allergenName = trim((string)$request->input('allergen_name'));
        if (empty($allergenName)) {
            Session::flash('error', 'يرجى إدخال اسم المادة المسببة للحساسية.');
            $this->redirect(url("/patients/{$id}"));
            return;
        }

        $this->allergyModel->create([
            'patient_id' => (int)$id,
            'allergen_type' => $request->input('allergen_type', 'drug'),
            'allergen_name' => $allergenName,
            'severity' => $request->input('severity', 'severe'),
            'reaction_notes' => trim((string)$request->input('reaction_notes')),
            'recorded_by' => auth()->id(),
        ]);

        AuditLog::log('ADD_PATIENT_ALLERGY', 'patient_allergies', $id, null, [
            'allergen' => $allergenName,
            'severity' => $request->input('severity'),
        ]);

        Session::flash('success', 'تم تسجيل التحسس الطبي في ملف المريض بنجاح.');
        $this->redirect(url("/patients/{$id}"));
    }

    public function addCondition(Request $request, Response $response, string $id): void
    {
        $patient = $this->patientModel->find($id);
        if (!$patient) {
            Session::flash('error', 'المريض غير موجود.');
            $this->redirect(url('/patients'));
            return;
        }

        $conditionName = trim((string)$request->input('condition_name'));
        if (empty($conditionName)) {
            Session::flash('error', 'يرجى إدخال اسم المرض المزمن.');
            $this->redirect(url("/patients/{$id}"));
            return;
        }

        $this->conditionModel->create([
            'patient_id' => (int)$id,
            'condition_name' => $conditionName,
            'icd10_code' => trim((string)$request->input('icd10_code')),
            'diagnosed_date' => $request->input('diagnosed_date'),
            'is_active' => 1,
        ]);

        AuditLog::log('ADD_PATIENT_CONDITION', 'patient_conditions', $id, null, ['condition' => $conditionName]);

        Session::flash('success', 'تم توثيق الحالة المرضية المزمنة بنجاح.');
        $this->redirect(url("/patients/{$id}"));
    }

    /**
     * Live Quick Search API for global top search and shortcut Ctrl+K
     */
    public function quickSearch(Request $request, Response $response): Response
    {
        $query = trim((string)$request->query('q', ''));
        if (mb_strlen($query) < 2) {
            return $response->json(['data' => []]);
        }

        $sql = "SELECT p.id, p.mrn, 
                       CONCAT(p.first_name, ' ', COALESCE(p.second_name, ''), ' ', p.last_name) AS full_name,
                       p.phone_primary, p.national_id, p.gender,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age
                FROM `patients` p
                WHERE `mrn` LIKE :q1 
                   OR `national_id` LIKE :q2 
                   OR `phone_primary` LIKE :q3 
                   OR `first_name` LIKE :q4 
                   OR `last_name` LIKE :q5 
                   OR CONCAT(`first_name`, ' ', COALESCE(`second_name`, ''), ' ', `last_name`) LIKE :q6
                ORDER BY p.id DESC
                LIMIT 8";

        $db = \App\Core\Database::getInstance();
        $term = "%{$query}%";
        $rows = $db->fetchAll($sql, [
            'q1' => $term,
            'q2' => $term,
            'q3' => $term,
            'q4' => $term,
            'q5' => $term,
            'q6' => $term,
        ]);

        $results = array_map(function($pt) {
            return [
                'id' => (int)$pt['id'],
                'mrn' => $pt['mrn'],
                'name' => trim(preg_replace('/\s+/', ' ', $pt['full_name'])),
                'phone' => $pt['phone_primary'],
                'national_id' => $pt['national_id'],
                'age' => $pt['age'],
                'gender' => $pt['gender'],
                'url' => url('/patients/' . $pt['id']),
                'emr_url' => url('/emr'),
            ];
        }, $rows);

        return $response->json(['data' => $results]);
    }
}
