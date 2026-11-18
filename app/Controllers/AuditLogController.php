<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    private AuditLog $auditLogModel;

    public function __construct()
    {
        $this->auditLogModel = new AuditLog();
    }

    public function index(Request $request, Response $response): string
    {
        if (!can('audit.view')) {
            Session::flash('error', 'ليس لديك صلاحية عرض سجل التدقيق والمراقبة.');
            $this->redirect(url('/'));
        }

        $page = (int)$request->query('page', 1);
        $filters = [
            'action' => trim((string)$request->query('action', '')),
            'table_name' => trim((string)$request->query('table_name', '')),
        ];

        $results = $this->auditLogModel->search($filters, $page, 25);

        return $this->view('audit_logs/index', [
            'pageTitle' => 'سجل التدقيق والمراقبة (Audit Trail)',
            'logs' => $results['data'],
            'filters' => $filters,
            'currentPage' => $results['current_page'],
            'totalPages' => $results['last_page'],
            'totalRecords' => $results['total'],
        ]);
    }
}
