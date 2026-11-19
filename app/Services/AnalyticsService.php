<?php

namespace App\Services;

use App\Core\Database;
use App\Models\SystemSetting;

class AnalyticsService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get Executive Overview Dashboard Metrics
     */
    public function getExecutiveOverview(?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?: date('Y-01-01');
        $endDate = $endDate ?: date('Y-m-d');

        // 1. Clinical Volume Metrics
        $totalPatients = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM `patients`");
        $periodVisits = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM `visits` WHERE DATE(`created_at`) BETWEEN :start AND :end",
            ['start' => $startDate, 'end' => $endDate]
        );
        $periodAdmissions = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM `admissions` WHERE `admission_date` BETWEEN :start AND :end",
            ['start' => $startDate, 'end' => $endDate]
        );
        $periodSurgeries = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM `surgeries` WHERE DATE(`scheduled_start`) BETWEEN :start AND :end",
            ['start' => $startDate, 'end' => $endDate]
        );
        $periodLabTests = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM `lab_orders` WHERE DATE(`created_at`) BETWEEN :start AND :end",
            ['start' => $startDate, 'end' => $endDate]
        );
        $periodRadiology = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM `radiology_orders` WHERE DATE(`created_at`) BETWEEN :start AND :end",
            ['start' => $startDate, 'end' => $endDate]
        );
        $periodErVisits = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM `visits` WHERE `visit_type` = 'er' AND DATE(`created_at`) BETWEEN :start AND :end",
            ['start' => $startDate, 'end' => $endDate]
        );

        // 2. Bed Occupancy & ALOS Metrics
        $totalBeds = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM `beds` WHERE `status` != 'maintenance'");
        $occupiedBeds = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM `beds` WHERE `status` = 'occupied'");
        $bedOccupancyRate = ($totalBeds > 0) ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0;

        // Average Length of Stay (ALOS) in days for discharged patients
        $alosRow = $this->db->fetchOne(
            "SELECT AVG(DATEDIFF(COALESCE(discharge_date, CURRENT_DATE), admission_date) + 1) as alos 
             FROM `admissions` WHERE `admission_date` BETWEEN :start AND :end",
            ['start' => $startDate, 'end' => $endDate]
        );
        $alos = round((float)($alosRow['alos'] ?? 0), 1);

        // 3. Financial Metrics (SDG)
        $billingRow = $this->db->fetchOne(
            "SELECT 
                COALESCE(SUM(total_gross), 0) as gross_billed,
                COALESCE(SUM(paid_amount), 0) as total_paid,
                COALESCE(SUM(total_patient_due - paid_amount), 0) as total_unpaid,
                COALESCE(SUM(total_insurance), 0) as total_insurance_portion
             FROM `invoices` WHERE DATE(`created_at`) BETWEEN :start AND :end",
            ['start' => $startDate, 'end' => $endDate]
        );

        // Collections breakdown (Cash vs Bankak)
        $paymentMethods = $this->db->fetchAll(
            "SELECT `payment_method`, COALESCE(SUM(amount), 0) as total_collected 
             FROM `payments` WHERE DATE(`created_at`) BETWEEN :start AND :end 
             GROUP BY `payment_method`",
            ['start' => $startDate, 'end' => $endDate]
        );
        $cashTotal = 0;
        $bankakTotal = 0;
        foreach ($paymentMethods as $pm) {
            if ($pm['payment_method'] === 'bankak') {
                $bankakTotal += (float)$pm['total_collected'];
            } else {
                $cashTotal += (float)$pm['total_collected'];
            }
        }

        // Insurance claims metrics
        $insuranceRow = $this->db->fetchOne(
            "SELECT 
                COALESCE(SUM(claim_amount), 0) as total_claimed,
                COALESCE(SUM(approved_amount), 0) as total_approved
             FROM `insurance_claims` WHERE `submission_date` BETWEEN :start AND :end",
            ['start' => $startDate, 'end' => $endDate]
        );

        // Payroll expenses
        $payrollRow = $this->db->fetchOne(
            "SELECT COALESCE(SUM(net_salary), 0) as total_salaries FROM `payroll`"
        );
        $totalSalaries = (float)($payrollRow['total_salaries'] ?? 0);

        // Net Operating Margin = Total Collected - Total Salaries
        $totalCollected = (float)($billingRow['total_paid'] ?? 0);
        $netOperatingIncome = $totalCollected - $totalSalaries;

        return [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'clinical' => [
                'total_patients'     => $totalPatients,
                'period_visits'      => $periodVisits,
                'period_admissions'  => $periodAdmissions,
                'period_surgeries'   => $periodSurgeries,
                'period_lab_tests'   => $periodLabTests,
                'period_radiology'   => $periodRadiology,
                'period_er_visits'   => $periodErVisits,
                'total_beds'         => $totalBeds,
                'occupied_beds'      => $occupiedBeds,
                'bed_occupancy_rate' => $bedOccupancyRate,
                'alos'               => $alos,
            ],
            'financial' => [
                'gross_billed'           => (float)($billingRow['gross_billed'] ?? 0),
                'total_collected'        => $totalCollected,
                'total_unpaid'           => (float)($billingRow['total_unpaid'] ?? 0),
                'insurance_portion'      => (float)($billingRow['total_insurance_portion'] ?? 0),
                'cash_collected'         => $cashTotal,
                'bankak_collected'       => $bankakTotal,
                'insurance_claimed'      => (float)($insuranceRow['total_claimed'] ?? 0),
                'insurance_approved'     => (float)($insuranceRow['total_approved'] ?? 0),
                'payroll_expenses'       => $totalSalaries,
                'net_operating_income'   => $netOperatingIncome,
            ],
        ];
    }

    /**
     * Get Clinical Departmental Distribution
     */
    public function getClinicalDepartmentBreakdown(?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?: date('Y-01-01');
        $endDate = $endDate ?: date('Y-m-d');

        $sql = "SELECT d.name_ar as department_name, COUNT(v.id) as visit_count
                FROM `departments` d
                LEFT JOIN `visits` v ON v.department_id = d.id AND DATE(v.created_at) BETWEEN :start AND :end
                GROUP BY d.id, d.name_ar
                ORDER BY visit_count DESC";

        return $this->db->fetchAll($sql, ['start' => $startDate, 'end' => $endDate]);
    }

    /**
     * Get Revenue by Service Type
     */
    public function getRevenueByServiceCategory(?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?: date('Y-01-01');
        $endDate = $endDate ?: date('Y-m-d');

        $sql = "SELECT ii.item_type, 
                       COUNT(ii.id) as transaction_count,
                       COALESCE(SUM(ii.total_amount), 0) as total_revenue
                FROM `invoice_items` ii
                INNER JOIN `invoices` inv ON ii.invoice_id = inv.id
                WHERE DATE(inv.created_at) BETWEEN :start AND :end
                GROUP BY ii.item_type
                ORDER BY total_revenue DESC";

        return $this->db->fetchAll($sql, ['start' => $startDate, 'end' => $endDate]);
    }

    /**
     * Get Pharmacy Stock & Expiry Analysis
     */
    public function getPharmacyStockMetrics(): array
    {
        $today = date('Y-m-d');
        $alertThreshold = date('Y-m-d', strtotime('+90 days'));

        $totalMedicines = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM `medicines` WHERE `is_active` = 1");
        
        $stockValueRow = $this->db->fetchOne(
            "SELECT COALESCE(SUM(b.quantity_on_hand * b.sale_price), 0) as total_stock_value,
                    COALESCE(SUM(b.quantity_on_hand), 0) as total_units
             FROM `medicine_batches` b
             INNER JOIN `medicines` m ON b.medicine_id = m.id
             WHERE b.quantity_on_hand > 0"
        );

        $nearExpiryCount = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM `medicine_batches` 
             WHERE `quantity_on_hand` > 0 AND `expiry_date` BETWEEN :today AND :alert_date",
            ['today' => $today, 'alert_date' => $alertThreshold]
        );

        $expiredCount = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM `medicine_batches` 
             WHERE `quantity_on_hand` > 0 AND `expiry_date` < :today",
            ['today' => $today]
        );

        return [
            'total_medicines'   => $totalMedicines,
            'total_stock_value' => (float)($stockValueRow['total_stock_value'] ?? 0),
            'total_units'       => (int)($stockValueRow['total_units'] ?? 0),
            'near_expiry_count' => $nearExpiryCount,
            'expired_count'     => $expiredCount,
        ];
    }
}
