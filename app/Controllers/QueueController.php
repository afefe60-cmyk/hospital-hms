<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Clinic;
use App\Models\Queue;

class QueueController extends Controller
{
    private Queue $queueModel;
    private Clinic $clinicModel;

    public function __construct()
    {
        $this->queueModel = new Queue();
        $this->clinicModel = new Clinic();
    }

    public function display(Request $request, Response $response, string $clinicId): string
    {
        $clinic = $this->clinicModel->find($clinicId);
        if (!$clinic) {
            $response->redirect(url('/'));
        }

        $activeQueue = $this->queueModel->activeQueueForClinic((int)$clinicId);
        $currentCall = $this->queueModel->currentlyCalling((int)$clinicId);

        return $this->view('queue/display', [
            'pageTitle' => "شاشة الانتظار: {$clinic['name_ar']} (غرفة {$clinic['room_number']})",
            'clinic' => $clinic,
            'activeQueue' => $activeQueue,
            'currentCall' => $currentCall,
        ], 'layouts/tv_display');
    }

    public function callNext(Request $request, Response $response, string $queueId): void
    {
        $queue = $this->queueModel->find($queueId);
        if ($queue) {
            $db = \App\Core\Database::getInstance();
            $sql = "UPDATE `queues` SET `status` = 'called', `called_time` = NOW() WHERE `id` = :id";
            $db->query($sql, ['id' => $queueId]);

            AuditLog::log('CALL_QUEUE_PATIENT', 'queues', $queueId, null, [
                'token' => $queue['queue_number'],
                'clinic_id' => $queue['clinic_id'],
            ]);

            Session::flash('success', "تم النداء على تذكرة رقم #{$queue['queue_number']}.");
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? url('/reception'));
    }

    public function complete(Request $request, Response $response, string $queueId): void
    {
        $queue = $this->queueModel->find($queueId);
        if ($queue) {
            $this->queueModel->update($queueId, ['status' => 'completed']);
            AuditLog::log('COMPLETE_QUEUE_PATIENT', 'queues', $queueId);
            Session::flash('success', "تم إنهاء الزيارة بنجاح.");
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? url('/reception'));
    }
}
