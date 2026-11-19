<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Services\AnalyticsService;
use App\Models\SystemSetting;

class ReportController extends Controller
{
    private AnalyticsService $analytics;
    private Database $db;

    public function __construct()
    {
        $this->analytics = new AnalyticsService();
        $this->db = Database::getInstance();
    }

    /**
     * Executive Overview & Analytics Dashboard
     */
    public function index(Request $request, Response $response): string
    {
        $startDate = $request->query('start_date') ?: date('Y-01-01');
        $endDate = $request->query('end_date') ?: date('Y-m-d');

        $metrics = $this->analytics->getExecutiveOverview($startDate, $endDate);
        $deptBreakdown = $this->analytics->getClinicalDepartmentBreakdown($startDate, $endDate);
        $serviceBreakdown = $this->analytics->getRevenueByServiceCategory($startDate, $endDate);
        $pharmacyMetrics = $this->analytics->getPharmacyStockMetrics();

        return $this->view('reports/index', [
            'metrics'          => $metrics,
            'deptBreakdown'    => $deptBreakdown,
            'serviceBreakdown' => $serviceBreakdown,
            'pharmacyMetrics'  => $pharmacyMetrics,
            'startDate'        => $startDate,
            'endDate'          => $endDate,
        ]);
    }

    /**
     * Financial & Revenue Comprehensive Report
     */
    public function financial(Request $request, Response $response): string
    {
        $startDate = $request->query('start_date') ?: date('Y-01-01');
        $endDate = $request->query('end_date') ?: date('Y-m-d');

        $metrics = $this->analytics->getExecutiveOverview($startDate, $endDate);
        $serviceBreakdown = $this->analytics->getRevenueByServiceCategory($startDate, $endDate);

        // Recent Invoices
        $invoices = $this->db->fetchAll(
            "SELECT i.*, DATE(i.created_at) as invoice_date, i.total_gross as total_amount,
                    (i.total_patient_due - i.paid_amount) as remaining_amount,
                    CONCAT(p.first_name, ' ', p.last_name) as patient_name, p.mrn
             FROM `invoices` i
             INNER JOIN `patients` p ON i.patient_id = p.id
             WHERE DATE(i.created_at) BETWEEN :start AND :end
             ORDER BY i.created_at DESC, i.id DESC LIMIT 50",
            ['start' => $startDate, 'end' => $endDate]
        );

        // Cash vs Bankak payments
        $payments = $this->db->fetchAll(
            "SELECT p.*, DATE(p.created_at) as payment_date, i.invoice_number,
                    CONCAT(pt.first_name, ' ', pt.last_name) as patient_name
             FROM `payments` p
             INNER JOIN `invoices` i ON p.invoice_id = i.id
             INNER JOIN `patients` pt ON i.patient_id = pt.id
             WHERE DATE(p.created_at) BETWEEN :start AND :end
             ORDER BY p.id DESC LIMIT 50",
            ['start' => $startDate, 'end' => $endDate]
        );

        return $this->view('reports/financial', [
            'metrics'          => $metrics,
            'serviceBreakdown' => $serviceBreakdown,
            'invoices'         => $invoices,
            'payments'         => $payments,
            'startDate'        => $startDate,
            'endDate'          => $endDate,
        ]);
    }

    /**
     * Clinical Quality & Inpatient Indicators Report
     */
    public function clinical(Request $request, Response $response): string
    {
        $startDate = $request->query('start_date') ?: date('Y-01-01');
        $endDate = $request->query('end_date') ?: date('Y-m-d');

        $metrics = $this->analytics->getExecutiveOverview($startDate, $endDate);
        $deptBreakdown = $this->analytics->getClinicalDepartmentBreakdown($startDate, $endDate);

        // Inpatient admissions list
        $admissions = $this->db->fetchAll(
            "SELECT a.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name, p.mrn,
                    d.name_ar as department_name,
                    COALESCE(w.name_ar, 'عنبر التنويم') as ward_name,
                    COALESCE(b.bed_number, '-') as bed_number
             FROM `admissions` a
             INNER JOIN `patients` p ON a.patient_id = p.id
             INNER JOIN `departments` d ON a.department_id = d.id
             LEFT JOIN `bed_assignments` ba ON ba.admission_id = a.id AND ba.is_active = 1
             LEFT JOIN `beds` b ON ba.bed_id = b.id
             LEFT JOIN `rooms` r ON b.room_id = r.id
             LEFT JOIN `wards` w ON r.ward_id = w.id
             WHERE a.admission_date BETWEEN :start AND :end
             ORDER BY a.admission_date DESC LIMIT 50",
            ['start' => $startDate, 'end' => $endDate]
        );

        // Surgeries list
        $surgeries = $this->db->fetchAll(
            "SELECT s.*, DATE(s.scheduled_start) as surgery_date, s.scheduled_start as start_time,
                    CONCAT(p.first_name, ' ', p.last_name) as patient_name, p.mrn,
                    CONCAT(d.first_name, ' ', d.last_name) as doctor_name
             FROM `surgeries` s
             INNER JOIN `patients` p ON s.patient_id = p.id
             INNER JOIN `doctors` doc ON s.lead_surgeon_id = doc.id
             INNER JOIN `employees` d ON doc.employee_id = d.id
             WHERE DATE(s.scheduled_start) BETWEEN :start AND :end
             ORDER BY s.scheduled_start DESC LIMIT 50",
            ['start' => $startDate, 'end' => $endDate]
        );

        return $this->view('reports/clinical', [
            'metrics'       => $metrics,
            'deptBreakdown' => $deptBreakdown,
            'admissions'    => $admissions,
            'surgeries'     => $surgeries,
            'startDate'     => $startDate,
            'endDate'       => $endDate,
        ]);
    }

    /**
     * Pharmacy & Medical Inventory Report
     */
    public function inventory(Request $request, Response $response): string
    {
        $pharmacyMetrics = $this->analytics->getPharmacyStockMetrics();

        // Expiring batches list
        $expiringBatches = $this->db->fetchAll(
            "SELECT b.*, m.commercial_name, m.scientific_name, m.strength, m.dosage_form,
                    w.name_ar as warehouse_name
             FROM `medicine_batches` b
             INNER JOIN `medicines` m ON b.medicine_id = m.id
             LEFT JOIN `warehouses` w ON b.warehouse_id = w.id
             WHERE b.quantity_on_hand > 0
             ORDER BY b.expiry_date ASC LIMIT 50"
        );

        return $this->view('reports/inventory', [
            'metrics'         => $pharmacyMetrics,
            'expiringBatches' => $expiringBatches,
        ]);
    }

    /**
     * Human Resources & Workforce Schedule Report
     */
    public function hr(Request $request, Response $response): string
    {
        $startDate = $request->query('start_date') ?: date('Y-m-01');
        $endDate = $request->query('end_date') ?: date('Y-m-t');

        $activeStaff = $this->db->fetchAll(
            "SELECT e.*, d.name_ar as department_name_ar
             FROM `employees` e
             INNER JOIN `departments` d ON e.department_id = d.id
             WHERE e.status = 'active'
             ORDER BY e.department_id ASC, e.first_name ASC"
        );

        $shiftCoverage = $this->db->fetchAll(
            "SELECT s.name_ar as shift_name, COUNT(r.id) as scheduled_count,
                    SUM(CASE WHEN r.status = 'attended' THEN 1 ELSE 0 END) as attended_count,
                    SUM(CASE WHEN r.status = 'absent' THEN 1 ELSE 0 END) as absent_count
             FROM `shifts` s
             LEFT JOIN `employee_rosters` r ON r.shift_id = s.id AND r.duty_date BETWEEN :start AND :end
             GROUP BY s.id, s.name_ar",
            ['start' => $startDate, 'end' => $endDate]
        );

        $payrollSummary = (new \App\Models\Payroll())->getSummaryStats((int)date('Y'), (int)date('m'));

        return $this->view('reports/hr', [
            'activeStaff'    => $activeStaff,
            'shiftCoverage'  => $shiftCoverage,
            'payrollSummary' => $payrollSummary,
            'startDate'      => $startDate,
            'endDate'        => $endDate,
        ]);
    }

    /**
     * Official Printable Executive Hospital Report (A4)
     */
    public function printReport(Request $request, Response $response): string
    {
        $startDate = $request->query('start_date') ?: date('Y-01-01');
        $endDate = $request->query('end_date') ?: date('Y-m-d');

        $metrics = $this->analytics->getExecutiveOverview($startDate, $endDate);
        $serviceBreakdown = $this->analytics->getRevenueByServiceCategory($startDate, $endDate);
        $pharmacyMetrics = $this->analytics->getPharmacyStockMetrics();
        $settings = (new SystemSetting())->allKeyValue();

        return $this->view('reports/print', [
            'metrics'          => $metrics,
            'serviceBreakdown' => $serviceBreakdown,
            'pharmacyMetrics'  => $pharmacyMetrics,
            'settings'         => $settings,
            'startDate'        => $startDate,
            'endDate'          => $endDate,
        ], null); // Render without main layout
    }

    /**
     * Export Report Data as CSV
     */
    public function exportCsv(Request $request, Response $response): void
    {
        $type = $request->query('type', 'financial');
        $startDate = $request->query('start_date') ?: date('Y-01-01');
        $endDate = $request->query('end_date') ?: date('Y-m-d');

        $filename = "hms_export_{$type}_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");

        // Output UTF-8 BOM for Excel Arabic support
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        if ($type === 'financial') {
            fputcsv($output, ['رقم الفاتورة', 'اسم المريض', 'رقم الملف', 'التاريخ', 'الإجمالي (SDG)', 'المدفوع', 'المتبقي', 'الحالة']);
            $rows = $this->db->fetchAll(
                "SELECT i.invoice_number, CONCAT(p.first_name, ' ', p.last_name) as patient_name, p.mrn,
                        DATE(i.created_at) as invoice_date, i.total_gross as total_amount, i.paid_amount,
                        (i.total_patient_due - i.paid_amount) as remaining_amount, i.status
                 FROM `invoices` i
                 INNER JOIN `patients` p ON i.patient_id = p.id
                 WHERE DATE(i.created_at) BETWEEN :start AND :end
                 ORDER BY i.id DESC",
                ['start' => $startDate, 'end' => $endDate]
            );
            foreach ($rows as $r) {
                fputcsv($output, array_values($r));
            }
        } elseif ($type === 'clinical') {
            fputcsv($output, ['رقم الدخول', 'اسم المريض', 'رقم الملف', 'تاريخ التنويم', 'العنبر', 'رقم السرير', 'حالة الدخول']);
            $rows = $this->db->fetchAll(
                "SELECT a.admission_number, CONCAT(p.first_name, ' ', p.last_name) as patient_name, p.mrn,
                        a.admission_date, COALESCE(w.name_ar, 'عنبر التنويم') as ward_name,
                        COALESCE(b.bed_number, '-') as bed_number, a.status
                 FROM `admissions` a
                 INNER JOIN `patients` p ON a.patient_id = p.id
                 LEFT JOIN `bed_assignments` ba ON ba.admission_id = a.id AND ba.is_active = 1
                 LEFT JOIN `beds` b ON ba.bed_id = b.id
                 LEFT JOIN `rooms` r ON b.room_id = r.id
                 LEFT JOIN `wards` w ON r.ward_id = w.id
                 WHERE a.admission_date BETWEEN :start AND :end
                 ORDER BY a.id DESC",
                ['start' => $startDate, 'end' => $endDate]
            );
            foreach ($rows as $r) {
                fputcsv($output, array_values($r));
            }
        }

        fclose($output);
        exit;
    }
}
