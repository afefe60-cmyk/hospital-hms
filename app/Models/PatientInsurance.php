<?php

namespace App\Models;

use App\Core\Model;

class PatientInsurance extends Model
{
    protected string $table = 'patient_insurance';
    protected bool $timestamps = false;

    protected array $fillable = [
        'patient_id',
        'company_id',
        'policy_number',
        'member_number',
        'coverage_percentage',
        'copay_limit',
        'start_date',
        'expiry_date',
        'is_active',
        'created_at',
    ];

    public function activePolicyForPatient(int $patientId): ?array
    {
        $sql = "SELECT pi.*, ic.name_ar AS company_name_ar, ic.name_en AS company_name_en, ic.code AS company_code
                FROM patient_insurance pi
                INNER JOIN insurance_companies ic ON pi.company_id = ic.id
                WHERE pi.patient_id = :pid AND pi.is_active = 1 AND pi.expiry_date >= CURDATE()
                ORDER BY pi.id DESC LIMIT 1";
        return $this->db->fetchOne($sql, ['pid' => $patientId]);
    }

    public function listWithDetails(?int $companyId = null, int $limit = 50): array
    {
        $sql = "SELECT pi.*,
                       ic.name_ar AS company_name, ic.code AS company_code,
                       pat.mrn, pat.phone_primary,
                       CONCAT(pat.first_name, ' ', pat.second_name, ' ', pat.last_name) AS patient_name
                FROM patient_insurance pi
                INNER JOIN insurance_companies ic ON pi.company_id = ic.id
                INNER JOIN patients pat ON pi.patient_id = pat.id
                WHERE 1=1";
        $params = [];
        if ($companyId) {
            $sql .= " AND pi.company_id = :cid";
            $params['cid'] = $companyId;
        }
        $sql .= " ORDER BY pi.id DESC LIMIT " . (int)$limit;
        return $this->db->fetchAll($sql, $params);
    }
}
