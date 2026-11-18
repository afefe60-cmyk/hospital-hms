<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Bed;

class BedController extends Controller
{
    private Bed $bedModel;

    public function __construct()
    {
        $this->bedModel = new Bed();
    }

    public function index(Request $request, Response $response): string
    {
        $matrix = $this->bedModel->matrix();
        $counts = $this->bedModel->countsByStatus();

        return $this->view('beds/matrix', [
            'pageTitle' => 'إدارة وتسكين الأسِرّة والأجنحة (Bed Matrix)',
            'matrix' => $matrix,
            'counts' => $counts,
        ]);
    }

    public function updateStatus(Request $request, Response $response, string $id): void
    {
        $bed = $this->bedModel->find($id);
        if (!$bed) {
            Session::flash('error', 'السرير المطلوب غير موجود.');
            $this->redirect(url('/beds'));
            return;
        }

        $newStatus = (string)$request->input('status');
        $oldStatus = $bed['status'];

        if ($this->bedModel->updateStatus((int)$id, $newStatus)) {
            AuditLog::log('UPDATE_BED_STATUS', 'beds', $id, ['status' => $oldStatus], ['status' => $newStatus]);
            Session::flash('success', "تم تحديث حالة السرير {$bed['bed_number']} إلى ({$newStatus}).");
        } else {
            Session::flash('error', 'حالة السرير المدخلة غير صحيحة.');
        }

        $this->redirect(url('/beds'));
    }
}
