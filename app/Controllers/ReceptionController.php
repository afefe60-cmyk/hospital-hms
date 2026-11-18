<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\Visit;

class ReceptionController extends Controller
{
    private Appointment $appointmentModel;
    private Patient $patientModel;
    private Queue $queueModel;
    private Clinic $clinicModel;

    public function __construct()
    {
        $this->appointmentModel = new Appointment();
        $this->patientModel = new Patient();
        $this->queueModel = new Queue();
        $this->clinicModel = new Clinic();
    }

    public function index(Request $request, Response $response): string
    {
        $today = date('Y-m-d');

        // Today's appointments
        $todayAppointments = $this->appointmentModel->allDetailed(['date' => $today], 1, 50);

        // Active queues
        $clinics = $this->clinicModel->allWithDepartment();
        $queueSummaries = [];
        foreach ($clinics as $c) {
            $queueList = $this->queueModel->activeQueueForClinic((int)$c['id']);
            $currentCall = $this->queueModel->currentlyCalling((int)$c['id']);
            if (!empty($queueList) || $currentCall) {
                $queueSummaries[] = [
                    'clinic' => $c,
                    'current_call' => $currentCall,
                    'waiting_count' => count(array_filter($queueList, fn($q) => $q['status'] === 'waiting')),
                    'total_today' => count($queueList),
                ];
            }
        }

        return $this->view('reception/index', [
            'pageTitle' => 'مكتب الاستقبال وإدارة المراجعين (Front Desk)',
            'today' => $today,
            'todayAppointments' => $todayAppointments['data'],
            'queueSummaries' => $queueSummaries,
        ]);
    }
}
