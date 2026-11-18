<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\Visit;

class AppointmentController extends Controller
{
    private Appointment $appointmentModel;
    private Doctor $doctorModel;
    private Clinic $clinicModel;
    private Patient $patientModel;
    private Visit $visitModel;
    private Queue $queueModel;

    public function __construct()
    {
        $this->appointmentModel = new Appointment();
        $this->doctorModel = new Doctor();
        $this->clinicModel = new Clinic();
        $this->patientModel = new Patient();
        $this->visitModel = new Visit();
        $this->queueModel = new Queue();
    }

    public function index(Request $request, Response $response): string
    {
        $page = (int)$request->query('page', 1);
        $date = $request->query('date', date('Y-m-d'));
        $status = $request->query('status', '');

        $results = $this->appointmentModel->allDetailed([
            'date' => $date,
            'status' => $status,
        ], $page, 20);

        return $this->view('appointments/index', [
            'pageTitle' => 'جدول المواعيد والحجوزات',
            'appointments' => $results['data'],
            'selectedDate' => $date,
            'selectedStatus' => $status,
            'currentPage' => $results['current_page'],
            'totalPages' => $results['last_page'],
            'totalRecords' => $results['total'],
        ]);
    }

    public function create(Request $request, Response $response): string
    {
        $patientId = (int)$request->query('patient_id', 0);
        $selectedPatient = $patientId > 0 ? $this->patientModel->find($patientId) : null;

        $doctors = $this->doctorModel->allDetailed();
        $clinics = $this->clinicModel->allWithDepartment();

        return $this->view('appointments/create', [
            'pageTitle' => 'حجز موعد عيادة جديد',
            'selectedPatient' => $selectedPatient,
            'doctors' => $doctors,
            'clinics' => $clinics,
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $validation = $this->validate($request->all(), [
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'clinic_id' => 'required',
            'appointment_date' => 'required',
            'appointment_time' => 'required',
        ]);

        if (!$validation['isValid']) {
            $this->redirect(url('/appointments/create'));
            return;
        }

        $clinicId = (int)$request->input('clinic_id');
        $date = (string)$request->input('appointment_date');
        $token = $this->appointmentModel->generateToken($clinicId, $date);

        $appId = $this->appointmentModel->create([
            'patient_id' => (int)$request->input('patient_id'),
            'doctor_id' => (int)$request->input('doctor_id'),
            'clinic_id' => $clinicId,
            'appointment_date' => $date,
            'appointment_time' => $request->input('appointment_time'),
            'token_number' => $token,
            'status' => 'booked',
            'created_by' => auth()->id(),
        ]);

        AuditLog::log('BOOK_APPOINTMENT', 'appointments', $appId, null, [
            'token' => $token,
            'date' => $date,
        ]);

        Session::flash('success', "تم حجز الموعد بنجاح. رقم الدور في العيادة: (#{$token})");
        $this->redirect(url('/appointments'));
    }

    public function checkIn(Request $request, Response $response, string $id): void
    {
        $appointment = $this->appointmentModel->find($id);
        if (!$appointment) {
            Session::flash('error', 'الموعد غير موجود.');
            $this->redirect(url('/appointments'));
            return;
        }

        $clinic = $this->clinicModel->find($appointment['clinic_id']);

        // 1. Update appointment status to checked_in
        $this->appointmentModel->update($id, ['status' => 'checked_in']);

        // 2. Create visit
        $visitNumber = $this->visitModel->generateVisitNumber();
        $visitId = $this->visitModel->create([
            'visit_number' => $visitNumber,
            'patient_id' => $appointment['patient_id'],
            'visit_type' => 'opd',
            'department_id' => $clinic['department_id'],
            'attending_doctor_id' => $appointment['doctor_id'],
            'arrival_time' => date('Y-m-d H:i:s'),
            'status' => 'active',
            'created_by' => auth()->id(),
        ]);

        // 3. Issue queue ticket
        $this->queueModel->create([
            'clinic_id' => $appointment['clinic_id'],
            'visit_id' => $visitId,
            'queue_number' => $appointment['token_number'],
            'status' => 'waiting',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLog::log('CHECK_IN_PATIENT', 'visits', $visitId, null, [
            'appointment_id' => $id,
            'visit_number' => $visitNumber,
            'token' => $appointment['token_number'],
        ]);

        Session::flash('success', "تم تسجيل وصول المريض وفتح الزيارة ({$visitNumber}) برقم التذكرة #{$appointment['token_number']}.");
        $this->redirect(url('/reception'));
    }

    public function cancel(Request $request, Response $response, string $id): void
    {
        $appointment = $this->appointmentModel->find($id);
        if ($appointment) {
            $reason = trim((string)$request->input('reason', 'إلغاء من طرف المريض'));
            $this->appointmentModel->update($id, [
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
            ]);
            AuditLog::log('CANCEL_APPOINTMENT', 'appointments', $id, null, ['reason' => $reason]);
            Session::flash('success', 'تم إلغاء الموعد.');
        }

        $this->redirect(url('/appointments'));
    }
}
