<?php

namespace App\Models;

use App\Core\Model;

class InsuranceClaim extends Model
{
    protected string $table = 'insurance_claims';
    protected bool $timestamps = true;

    protected array $fillable = [
        'claim_number',
        'company_id',
        'patient_id',
        'invoice_id',
        'policy_number',
        'claim_amount',
        'approved_amount',
        'rejected_amount',
        'status',
        'submission_date',
        'settlement_date',
        'rejection_reason',
        'notes',
        'created_by',
        'created_at',
        'updated_at',
    ];

    public function generateClaimNumber(): string
    {
        $prefix = 'CLM-' . date('Ym') . '-';
        $sql = "SELECT claim_number FROM `insurance_claims` WHERE `claim_number` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
        $last = $this->db->fetchColumn($sql, ['prefix' => "{$prefix}%"]);

        if ($last) {
            $numPart = (int)substr($last, strlen($prefix));
            $next = $numPart + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad((string)$next, 5, '0', STR_PAD_LEFT);
    }

    public static function calculateInsuranceSplit(float $gross, float $coveragePercentage, ?float $copayLimit = null): array
    {
        $percentage = min(100.0, max(0.0, $coveragePercentage));
        $calculatedIns = ($gross * $percentage) / 100.0;

        if ($copayLimit !== null && $copayLimit > 0) {
            $calculatedIns = min($calculatedIns, $copayLimit);
        }

        $patientShare = max(0.0, $gross - $calculatedIns);

        return [
            'gross' => $gross,
            'insurance_claim' => round($calculatedIns, 2),
            'patient_due' => round($patientShare, 2),
            'coverage_rate' => $percentage,
        ];
    }

    public function detailed(int $id): ?array
    {
        $sql = "SELECT c.*,
                       ic.name_ar AS company_name_ar, ic.name_en AS company_name_en, ic.code AS company_code, ic.phone AS company_phone,
                       p.mrn, p.gender AS patient_gender, p.date_of_birth, p.blood_group, p.phone_primary,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                       inv.invoice_number, inv.total_gross, inv.total_discount, inv.total_patient_due, inv.paid_amount,
                       u.full_name AS creator_name
                FROM `insurance_claims` c
                INNER JOIN `insurance_companies` ic ON c.company_id = ic.id
                INNER JOIN `patients` p ON c.patient_id = p.id
                INNER JOIN `invoices` inv ON c.invoice_id = inv.id
                LEFT JOIN `users` u ON c.created_by = u.id
                WHERE c.id = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    public function listWithDetails(?int $companyId = null, ?string $status = null, int $limit = 50): array
    {
        $sql = "SELECT c.*,
                       ic.name_ar AS company_name, ic.code AS company_code,
                       p.mrn,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       inv.invoice_number
                FROM `insurance_claims` c
                INNER JOIN `insurance_companies` ic ON c.company_id = ic.id
                INNER JOIN `patients` p ON c.patient_id = p.id
                INNER JOIN `invoices` inv ON c.invoice_id = inv.id
                WHERE 1=1";
        $params = [];

        if ($companyId) {
            $sql .= " AND c.company_id = :cid";
            $params['cid'] = $companyId;
        }

        if ($status) {
            $sql .= " AND c.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY c.id DESC LIMIT " . (int)$limit;
        return $this->db->fetchAll($sql, $params);
    }
}
