<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\SystemSetting;
use App\Services\AnalyticsService;

class HomeController extends Controller
{
    private AnalyticsService $analytics;

    public function __construct()
    {
        $this->analytics = new AnalyticsService();
    }

    public function index(Request $request, Response $response): string
    {
        $db = Database::getInstance();
        $today = date('Y-m-d');

        // Hospital settings
        $hospitalInfo = [
            'name_ar' => SystemSetting::get('hospital_name_ar', 'مستشفى الأمل التخصصي'),
            'name_en' => SystemSetting::get('hospital_name_en', 'Al-Amal Specialized Hospital'),
            'phone' => SystemSetting::get('hospital_phone', '+249 183 770000'),
            'emergency' => SystemSetting::get('emergency_hotline', '999 / +249 183 770111'),
            'bankak_account' => SystemSetting::get('bankak_account_number', '1820499'),
            'bankak_name' => SystemSetting::get('bankak_account_name', 'مستشفى الأمل التخصصي - بنكك'),
            'currency' => config('app.currency', 'SDG'),
        ];

        // 1. Key Operational Metrics for Today
        $totalPatients = (int)$db->fetchColumn("SELECT COUNT(*) FROM `patients`");
        $todayVisits = (int)$db->fetchColumn("SELECT COUNT(*) FROM `visits` WHERE DATE(`created_at`) = :today", ['today' => $today]);
        $activeInpatients = (int)$db->fetchColumn("SELECT COUNT(*) FROM `admissions` WHERE `status` = 'admitted'");
        
        $totalBeds = (int)$db->fetchColumn("SELECT COUNT(*) FROM `beds` WHERE `status` != 'maintenance'");
        $occupiedBeds = (int)$db->fetchColumn("SELECT COUNT(*) FROM `beds` WHERE `status` = 'occupied'");
        $availableBeds = (int)$db->fetchColumn("SELECT COUNT(*) FROM `beds` WHERE `status` = 'available'");
        $bedOccupancyRate = ($totalBeds > 0) ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0;

        $todaySurgeries = (int)$db->fetchColumn("SELECT COUNT(*) FROM `surgeries` WHERE DATE(`scheduled_start`) = :today", ['today' => $today]);
        $todayErCases = (int)$db->fetchColumn("SELECT COUNT(*) FROM `emergency_cases` WHERE DATE(`arrival_time`) = :today", ['today' => $today]);
        if ($todayErCases === 0) {
            $todayErCases = (int)$db->fetchColumn("SELECT COUNT(*) FROM `visits` WHERE `visit_type` = 'er' AND DATE(`created_at`) = :today", ['today' => $today]);
        }
        $todayLabOrders = (int)$db->fetchColumn("SELECT COUNT(*) FROM `lab_orders` WHERE DATE(`created_at`) = :today", ['today' => $today]);

        // Financial collections today (SDG)
        $todayPayments = $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total_today,
                    COALESCE(SUM(CASE WHEN payment_method = 'bankak' THEN amount ELSE 0 END), 0) as bankak_today,
                    COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN amount ELSE 0 END), 0) as cash_today
             FROM `payments` WHERE DATE(`created_at`) = :today",
            ['today' => $today]
        );
        $todayTotalSDG = (float)($todayPayments['total_today'] ?? 0);
        $todayBankakSDG = (float)($todayPayments['bankak_today'] ?? 0);
        $todayCashSDG = (float)($todayPayments['cash_today'] ?? 0);

        // Pharmacy stock indicators
        $pharmacyMetrics = $this->analytics->getPharmacyStockMetrics();

        // 2. Ward Occupancy Distribution
        $wardOccupancy = $db->fetchAll(
            "SELECT w.id, w.name_ar, w.gender_type,
                    COUNT(b.id) as total_beds,
                    SUM(CASE WHEN b.status = 'occupied' THEN 1 ELSE 0 END) as occupied_beds,
                    SUM(CASE WHEN b.status = 'available' THEN 1 ELSE 0 END) as available_beds
             FROM `wards` w
             LEFT JOIN `rooms` r ON r.ward_id = w.id
             LEFT JOIN `beds` b ON b.room_id = r.id AND b.status != 'maintenance'
             GROUP BY w.id, w.name_ar, w.gender_type
             ORDER BY w.id ASC"
        );

        // 3. Today's / Upcoming Surgeries (up to 5)
        $recentSurgeries = $db->fetchAll(
            "SELECT s.id, s.surgery_number, s.procedure_name, s.scheduled_start, s.status,
                    CONCAT(p.first_name, ' ', p.last_name) as patient_name, p.mrn,
                    op.name_ar as operating_room,
                    CONCAT(e.first_name, ' ', e.last_name) as surgeon_name
             FROM `surgeries` s
             INNER JOIN `patients` p ON s.patient_id = p.id
             LEFT JOIN `operating_rooms` op ON s.operating_room_id = op.id
             LEFT JOIN `doctors` d ON s.lead_surgeon_id = d.id
             LEFT JOIN `employees` e ON d.employee_id = e.id
             ORDER BY s.scheduled_start DESC
             LIMIT 5"
        );

        // 4. Live Emergency Department Cases (up to 5)
        $activeErCases = $db->fetchAll(
            "SELECT er.id, er.case_number, er.triage_level, er.arrival_time, er.status, er.chief_complaint,
                    COALESCE(CONCAT(p.first_name, ' ', p.last_name), er.temporary_patient_name, 'مريض طارئ') as patient_name
             FROM `emergency_cases` er
             LEFT JOIN `patients` p ON er.patient_id = p.id
             WHERE er.status IN ('triaged', 'under_treatment', 'arrived')
             ORDER BY er.id DESC
             LIMIT 5"
        );

        // 5. Critical FEFO Batches Near Expiry (< 90 days)
        $nearExpiryBatches = $db->fetchAll(
            "SELECT b.batch_number, b.expiry_date, b.quantity_on_hand, b.sale_price,
                    m.commercial_name, m.scientific_name,
                    DATEDIFF(b.expiry_date, CURRENT_DATE) as days_left
             FROM `medicine_batches` b
             INNER JOIN `medicines` m ON b.medicine_id = m.id
             WHERE b.quantity_on_hand > 0 AND b.expiry_date <= DATE_ADD(CURRENT_DATE, INTERVAL 90 DAY)
             ORDER BY b.expiry_date ASC
             LIMIT 5"
        );

        // Technical System info (for footer/diagnostics check)
        $systemInfo = [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'php_version' => PHP_VERSION,
            'db_status' => true,
            'timezone' => date_default_timezone_get(),
            'current_time' => date('Y-m-d H:i:s'),
            'locale' => Session::get('locale', config('app.locale', 'ar')),
            'currency' => config('app.currency', 'SDG'),
        ];

        return $this->view('home', [
            'pageTitle' => 'لوحة القيادة والعمليات اليومية',
            'hospital' => $hospitalInfo,
            'metrics' => [
                'total_patients' => $totalPatients,
                'today_visits' => $todayVisits,
                'active_inpatients' => $activeInpatients,
                'total_beds' => $totalBeds,
                'occupied_beds' => $occupiedBeds,
                'available_beds' => $availableBeds,
                'bed_occupancy_rate' => $bedOccupancyRate,
                'today_surgeries' => $todaySurgeries,
                'today_er_cases' => $todayErCases,
                'today_lab_orders' => $todayLabOrders,
                'today_collections_sdg' => $todayTotalSDG,
                'today_bankak_sdg' => $todayBankakSDG,
                'today_cash_sdg' => $todayCashSDG,
                'near_expiry_count' => $pharmacyMetrics['near_expiry_count'] ?? 0,
                'stock_value_sdg' => $pharmacyMetrics['total_stock_value'] ?? 0,
            ],
            'wardOccupancy' => $wardOccupancy,
            'recentSurgeries' => $recentSurgeries,
            'activeErCases' => $activeErCases,
            'nearExpiryBatches' => $nearExpiryBatches,
            'info' => $systemInfo,
        ]);
    }

    public function switchLanguage(Request $request, Response $response): void
    {
        $lang = $request->input('lang', 'ar');
        if (in_array($lang, ['ar', 'en'])) {
            Session::set('locale', $lang);
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        $this->redirect($referer);
    }
}
