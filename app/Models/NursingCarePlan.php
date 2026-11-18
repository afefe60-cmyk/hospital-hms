<?php

namespace App\Models;

use App\Core\Model;

class NursingCarePlan extends Model
{
    protected string $table = 'nursing_care_plans';
    protected bool $timestamps = false;

    protected array $fillable = [
        'admission_id',
        'nursing_diagnosis',
        'goals',
        'interventions',
        'evaluation',
        'status',
        'created_by',
        'created_at',
    ];

    public function forAdmission(int $admissionId): array
    {
        $sql = "SELECT cp.*, u.full_name AS creator_name
                FROM `nursing_care_plans` cp
                INNER JOIN `users` u ON cp.created_by = u.id
                WHERE cp.admission_id = :id
                ORDER BY cp.id DESC";
        return $this->db->fetchAll($sql, ['id' => $admissionId]);
    }
}
