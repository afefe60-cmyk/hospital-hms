<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\RadiologyOrder;
use App\Models\RadiologyReport;
use App\Models\RadiologyService;

class RadiologyController extends Controller
{
    private RadiologyOrder $orderModel;
    private RadiologyService $serviceModel;
    private RadiologyReport $reportModel;
    private Patient $patientModel;
    private Doctor $doctorModel;

    public function __construct()
    {
        $this->orderModel = new RadiologyOrder();
        $this->serviceModel = new RadiologyService();
        $this->reportModel = new RadiologyReport();
        $this->patientModel = new Patient();
        $this->doctorModel = new Doctor();
    }

    public function index(Request $request, Response $response): string
    {
        $db = \App\Core\Database::getInstance();
        $status = $request->query('status', 'all');
        $modality = $request->query('modality', 'all');

        $where = "WHERE 1=1";
        $params = [];

        if ($status !== 'all') {
            $where .= " AND ro.status = :status";
            $params['status'] = $status;
        }

        if ($modality !== 'all') {
            $where .= " AND rs.modality = :modality";
            $params['modality'] = $modality;
        }

        $sql = "SELECT ro.*,
                       p.mrn, p.gender,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS doctor_name,
                       rs.name_ar AS service_name, rs.modality, rs.price,
                       rr.id AS report_id
                FROM `radiology_orders` ro
                INNER JOIN `patients` p ON ro.patient_id = p.id
                INNER JOIN `doctors` doc ON ro.doctor_id = doc.id
                INNER JOIN `employees` e ON doc.employee_id = e.id
                INNER JOIN `radiology_services` rs ON ro.service_id = rs.id
                LEFT JOIN `radiology_reports` rr ON ro.id = rr.radiology_order_id
                {$where}
                ORDER BY FIELD(ro.priority, 'stat', 'urgent', 'routine') ASC, ro.created_at DESC";
        $orders = $db->fetchAll($sql, $params);

        $counts = [
            'ordered' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `radiology_orders` WHERE `status` = 'ordered'"),
            'scheduled' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `radiology_orders` WHERE `status` = 'scheduled'"),
            'performed' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `radiology_orders` WHERE `status` = 'performed'"),
            'reported' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `radiology_orders` WHERE `status` = 'reported'"),
            'total' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `radiology_orders`"),
        ];

        return $this->view('radiology/index', [
            'pageTitle' => 'نظام الأشعة والتصوير الطبي (Radiology Information System - RIS)',
            'orders' => $orders,
            'status' => $status,
            'modality' => $modality,
            'counts' => $counts,
        ]);
    }

    public function create(Request $request, Response $response): string
    {
        $patients = $this->patientModel->all('first_name', 'ASC');
        $doctors = $this->doctorModel->all();
        $services = $this->serviceModel->where('is_active', 1);

        $preselectedPatientId = $request->query('patient_id');

        return $this->view('radiology/create', [
            'pageTitle' => 'طلب تصوير وأشعة تشخيصية جديد (New Radiology Order)',
            'patients' => $patients,
            'doctors' => $doctors,
            'services' => $services,
            'preselectedPatientId' => $preselectedPatientId,
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $patientId = (int)$request->input('patient_id');
        $doctorId = (int)$request->input('doctor_id');
        $serviceId = (int)$request->input('service_id');
        $priority = $request->input('priority', 'routine');
        $indication = trim((string)$request->input('clinical_indication'));

        if ($patientId <= 0 || $doctorId <= 0 || $serviceId <= 0 || empty($indication)) {
            Session::flash('error', 'يرجى استيفاء الحقول واختيار الفحص الإشعاعي المطلوب وتوضيح الداعي السريري.');
            $this->redirect(url('/radiology/create'));
            return;
        }

        $orderNumber = $this->orderModel->generateOrderNumber();
        $orderId = $this->orderModel->create([
            'order_number' => $orderNumber,
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'service_id' => $serviceId,
            'priority' => $priority,
            'clinical_indication' => $indication,
            'status' => 'ordered',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLog::log('CREATE_RADIOLOGY_ORDER', 'radiology_orders', $orderId, null, [
            'order_number' => $orderNumber,
            'service_id' => $serviceId,
        ]);

        Session::flash('success', "تم تسجيل طلب الأشعة بنجاح بالرقم ({$orderNumber}).");
        $this->redirect(url("/radiology/{$orderId}"));
    }

    public function show(Request $request, Response $response, string $id): string
    {
        $order = $this->orderModel->detailed((int)$id);
        if (!$order) {
            Session::flash('error', 'طلب الأشعة غير موجود.');
            $this->redirect(url('/radiology'));
        }

        $report = $this->orderModel->report((int)$id);
        $attachments = $this->orderModel->attachments((int)$id);
        $doctors = $this->doctorModel->all();

        return $this->view('radiology/show', [
            'pageTitle' => "طلب أشعة - {$order['order_number']}",
            'order' => $order,
            'report' => $report,
            'attachments' => $attachments,
            'doctors' => $doctors,
        ]);
    }

    public function updateStatus(Request $request, Response $response, string $id): void
    {
        $order = $this->orderModel->find($id);
        if (!$order) {
            Session::flash('error', 'طلب الأشعة غير موجود.');
            $this->redirect(url('/radiology'));
            return;
        }

        $status = $request->input('status', 'performed');
        $this->orderModel->update($id, ['status' => $status]);

        AuditLog::log('UPDATE_RADIOLOGY_STATUS', 'radiology_orders', $id, ['old' => $order['status']], ['new' => $status]);

        Session::flash('success', "تم تحديث حالة طلب الأشعة إلى ({$status}) بنجاح.");
        $this->redirect(url("/radiology/{$id}"));
    }

    public function saveReport(Request $request, Response $response, string $id): void
    {
        $order = $this->orderModel->find($id);
        if (!$order) {
            Session::flash('error', 'طلب الأشعة غير موجود.');
            $this->redirect(url('/radiology'));
            return;
        }

        $findings = trim((string)$request->input('findings'));
        $impression = trim((string)$request->input('impression'));
        $radiologistId = (int)$request->input('radiologist_id');
        $status = $request->input('report_status', 'final');

        if (empty($findings) || empty($impression) || $radiologistId <= 0) {
            Session::flash('error', 'يرجى إدخال المشاهدات الإشعاعية والتشخيص الاستنتاجي وتحديد الطبيب الاستشاري.');
            $this->redirect(url("/radiology/{$id}"));
            return;
        }

        $existing = $this->orderModel->report((int)$id);
        $now = date('Y-m-d H:i:s');

        if ($existing) {
            $this->reportModel->update($existing['id'], [
                'radiologist_id' => $radiologistId,
                'findings' => $findings,
                'impression' => $impression,
                'reported_at' => $now,
                'status' => $status,
            ]);
        } else {
            $this->reportModel->create([
                'radiology_order_id' => (int)$id,
                'radiologist_id' => $radiologistId,
                'findings' => $findings,
                'impression' => $impression,
                'reported_at' => $now,
                'status' => $status,
                'created_at' => $now,
            ]);
        }

        $this->orderModel->update($id, ['status' => 'reported']);

        AuditLog::log('SAVE_RADIOLOGY_REPORT', 'radiology_reports', $id);

        Session::flash('success', 'تم اعتماد تقرير الأشعة التشخيصي بنجاح.');
        $this->redirect(url("/radiology/{$id}"));
    }

    public function printReport(Request $request, Response $response, string $id): string
    {
        $order = $this->orderModel->detailed((int)$id);
        if (!$order) {
            $response->redirect(url('/radiology'));
        }

        $report = $this->orderModel->report((int)$id);

        return $this->view('radiology/print_report', [
            'pageTitle' => "تقرير أشعة معتمد - {$order['order_number']}",
            'order' => $order,
            'report' => $report,
        ], null); // Render without layout for printing
    }
}
