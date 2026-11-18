<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\LabOrder;
use App\Models\LabResult;
use App\Models\LabResultItem;
use App\Models\LabSample;
use App\Models\LabTest;
use App\Models\Patient;

class LabController extends Controller
{
    private LabOrder $orderModel;
    private LabTest $testModel;
    private LabSample $sampleModel;
    private LabResult $resultModel;
    private LabResultItem $resultItemModel;
    private Patient $patientModel;
    private Doctor $doctorModel;

    public function __construct()
    {
        $this->orderModel = new LabOrder();
        $this->testModel = new LabTest();
        $this->sampleModel = new LabSample();
        $this->resultModel = new LabResult();
        $this->resultItemModel = new LabResultItem();
        $this->patientModel = new Patient();
        $this->doctorModel = new Doctor();
    }

    public function index(Request $request, Response $response): string
    {
        $db = \App\Core\Database::getInstance();
        $status = $request->query('status', 'all');

        $where = "WHERE 1=1";
        $params = [];

        if ($status !== 'all') {
            $where .= " AND lo.status = :status";
            $params['status'] = $status;
        }

        $sql = "SELECT lo.*,
                       p.mrn, p.gender,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS doctor_name,
                       ls.sample_barcode, ls.status AS sample_status,
                       lr.status AS result_status,
                       COUNT(loi.id) AS tests_count,
                       COALESCE(SUM(loi.price), 0.00) AS total_amount
                FROM `lab_orders` lo
                INNER JOIN `patients` p ON lo.patient_id = p.id
                INNER JOIN `doctors` doc ON lo.doctor_id = doc.id
                INNER JOIN `employees` e ON doc.employee_id = e.id
                LEFT JOIN `lab_order_items` loi ON lo.id = loi.lab_order_id
                LEFT JOIN `lab_samples` ls ON lo.id = ls.lab_order_id
                LEFT JOIN `lab_results` lr ON lo.id = lr.lab_order_id
                {$where}
                GROUP BY lo.id
                ORDER BY FIELD(lo.priority, 'stat', 'urgent', 'routine') ASC, lo.created_at DESC";
        $orders = $db->fetchAll($sql, $params);

        $counts = [
            'ordered' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `lab_orders` WHERE `status` = 'ordered'"),
            'sample_collected' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `lab_orders` WHERE `status` = 'sample_collected'"),
            'in_progress' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `lab_orders` WHERE `status` = 'in_progress'"),
            'completed' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `lab_orders` WHERE `status` = 'completed'"),
            'total' => (int)$db->fetchColumn("SELECT COUNT(*) FROM `lab_orders`"),
        ];

        return $this->view('lab/index', [
            'pageTitle' => 'نظام المختبرات الطبية (Laboratory Information System - LIS)',
            'orders' => $orders,
            'status' => $status,
            'counts' => $counts,
        ]);
    }

    public function create(Request $request, Response $response): string
    {
        $patients = $this->patientModel->all('first_name', 'ASC');
        $doctors = $this->doctorModel->all();
        $tests = $this->testModel->where('is_active', 1);

        $preselectedPatientId = $request->query('patient_id');

        return $this->view('lab/create', [
            'pageTitle' => 'إصدار طلب فحص معملي جديد (New Lab Order)',
            'patients' => $patients,
            'doctors' => $doctors,
            'tests' => $tests,
            'preselectedPatientId' => $preselectedPatientId,
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $patientId = (int)$request->input('patient_id');
        $doctorId = (int)$request->input('doctor_id');
        $priority = $request->input('priority', 'routine');
        $indication = trim((string)$request->input('clinical_indication'));
        $testIds = $request->input('test_ids', []);

        if ($patientId <= 0 || $doctorId <= 0 || empty($testIds)) {
            Session::flash('error', 'يرجى اختيار المريض والطبيب وتحديد فحص معملي واحد على الأقل.');
            $this->redirect(url('/lab/create'));
            return;
        }

        $orderNumber = $this->orderModel->generateOrderNumber();
        $orderId = $this->orderModel->create([
            'order_number' => $orderNumber,
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'priority' => $priority,
            'clinical_indication' => $indication,
            'status' => 'ordered',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $db = \App\Core\Database::getInstance();
        foreach ($testIds as $tid) {
            $test = $this->testModel->find($tid);
            if ($test) {
                $db->query("INSERT INTO `lab_order_items` (`lab_order_id`, `lab_test_id`, `price`)
                            VALUES (:oid, :tid, :price)", [
                    'oid' => $orderId,
                    'tid' => $test['id'],
                    'price' => $test['price'],
                ]);
            }
        }

        AuditLog::log('CREATE_LAB_ORDER', 'lab_orders', $orderId, null, [
            'order_number' => $orderNumber,
            'tests_count' => count($testIds),
        ]);

        Session::flash('success', "تم إنشاء الطلب المعملي بنجاح بالرقم ({$orderNumber}).");
        $this->redirect(url("/lab/{$orderId}"));
    }

    public function show(Request $request, Response $response, string $id): string
    {
        $order = $this->orderModel->detailed((int)$id);
        if (!$order) {
            Session::flash('error', 'الطلب المعملي غير موجود.');
            $this->redirect(url('/lab'));
        }

        $items = $this->orderModel->items((int)$id);
        $sample = $this->orderModel->sample((int)$id);
        $result = $this->orderModel->result((int)$id);
        $resultItems = $result ? $this->resultModel->items((int)$result['id']) : [];

        return $this->view('lab/show', [
            'pageTitle' => "ملف الفحص المعملي - {$order['order_number']}",
            'order' => $order,
            'items' => $items,
            'sample' => $sample,
            'result' => $result,
            'resultItems' => $resultItems,
        ]);
    }

    public function collectSample(Request $request, Response $response, string $id): void
    {
        $order = $this->orderModel->find($id);
        if (!$order) {
            Session::flash('error', 'الطلب المعملي غير موجود.');
            $this->redirect(url('/lab'));
            return;
        }

        $sampleType = $request->input('sample_type', 'whole_blood');
        $barcode = $this->sampleModel->generateBarcode();

        $this->sampleModel->create([
            'lab_order_id' => (int)$id,
            'sample_barcode' => $barcode,
            'sample_type' => $sampleType,
            'collected_at' => date('Y-m-d H:i:s'),
            'collected_by' => auth()->id(),
            'status' => 'received',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->orderModel->update($id, ['status' => 'sample_collected']);

        AuditLog::log('COLLECT_LAB_SAMPLE', 'lab_samples', $id, null, ['barcode' => $barcode]);

        Session::flash('success', "تم سحب العينة وتوليد الباركود المعملي ({$barcode}) بنجاح.");
        $this->redirect(url("/lab/{$id}"));
    }

    public function saveResults(Request $request, Response $response, string $id): void
    {
        $order = $this->orderModel->find($id);
        if (!$order) {
            Session::flash('error', 'الطلب المعملي غير موجود.');
            $this->redirect(url('/lab'));
            return;
        }

        $paramNames = $request->input('parameter_name', []);
        $testIds = $request->input('lab_test_id', []);
        $resultValues = $request->input('result_value', []);
        $units = $request->input('unit', []);
        $ranges = $request->input('reference_range', []);
        $flags = $request->input('flag', []);
        $comments = trim((string)$request->input('general_comments'));

        if (empty($paramNames)) {
            Session::flash('error', 'يرجى إدخال نتائج الفحص المعملي.');
            $this->redirect(url("/lab/{$id}"));
            return;
        }

        $db = \App\Core\Database::getInstance();

        // Check if result record already exists
        $result = $this->orderModel->result((int)$id);
        if ($result) {
            $resultId = $result['id'];
            $this->resultModel->update($resultId, ['general_comments' => $comments]);
            // Delete previous items to replace with updated
            $db->query("DELETE FROM `lab_result_items` WHERE `lab_result_id` = :rid", ['rid' => $resultId]);
        } else {
            $resultId = $this->resultModel->create([
                'lab_order_id' => (int)$id,
                'status' => 'draft',
                'general_comments' => $comments,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        for ($i = 0; $i < count($paramNames); $i++) {
            $val = trim((string)($resultValues[$i] ?? ''));
            if (!empty($val)) {
                $this->resultItemModel->create([
                    'lab_result_id' => $resultId,
                    'lab_test_id' => (int)($testIds[$i] ?? 1),
                    'parameter_name' => trim((string)$paramNames[$i]),
                    'result_value' => $val,
                    'unit' => trim((string)($units[$i] ?? '')),
                    'reference_range' => trim((string)($ranges[$i] ?? '')),
                    'flag' => $flags[$i] ?? 'normal',
                ]);
            }
        }

        $this->orderModel->update($id, ['status' => 'in_progress']);

        AuditLog::log('SAVE_LAB_RESULTS', 'lab_results', $resultId);

        Session::flash('success', 'تم حفظ نتائج الفحوصات المعملية بنجاح.');
        $this->redirect(url("/lab/{$id}"));
    }

    public function approveResults(Request $request, Response $response, string $id): void
    {
        $result = $this->orderModel->result((int)$id);
        if (!$result) {
            Session::flash('error', 'لم يتم إدخال نتائج لاعتمادها.');
            $this->redirect(url("/lab/{$id}"));
            return;
        }

        $this->resultModel->update($result['id'], [
            'status' => 'approved',
            'verified_by' => auth()->id(),
            'verified_at' => date('Y-m-d H:i:s'),
        ]);

        $this->orderModel->update($id, ['status' => 'completed']);

        AuditLog::log('APPROVE_LAB_RESULTS', 'lab_results', $result['id']);

        Session::flash('success', 'تم اعتماد تقرير النتائج المخبرية رسمياً وإغلاق الطلب.');
        $this->redirect(url("/lab/{$id}"));
    }

    public function printReport(Request $request, Response $response, string $id): string
    {
        $order = $this->orderModel->detailed((int)$id);
        if (!$order) {
            $response->redirect(url('/lab'));
        }

        $sample = $this->orderModel->sample((int)$id);
        $result = $this->orderModel->result((int)$id);
        $resultItems = $result ? $this->resultModel->items((int)$result['id']) : [];

        return $this->view('lab/print_report', [
            'pageTitle' => "تقرير مختبري معتمد - {$order['order_number']}",
            'order' => $order,
            'sample' => $sample,
            'result' => $result,
            'resultItems' => $resultItems,
        ], null); // Render without layout for printing
    }
}
