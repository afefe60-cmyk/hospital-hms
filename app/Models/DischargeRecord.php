<?php

namespace App\Models;

use App\Core\Model;

class DischargeRecord extends Model
{
    protected string $table = 'discharge_records';
    protected bool $timestamps = false;

    protected array $fillable = [
        'admission_id',
        'discharge_type',
        'discharge_summary',
        'discharge_condition',
        'discharge_doctor_id',
        'discharged_at',
        'created_at',
    ];

    public function detailed(int $admissionId): ?array
    {
        $sql = "SELECT dr.*,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS doctor_name,
                       s.name_ar AS doctor_specialty
                FROM `discharge_records` dr
                INNER JOIN `doctors` d ON dr.discharge_doctor_id = d.id
                INNER JOIN `employees` e ON d.employee_id = e.id
                INNER JOIN `specialties` s ON d.specialty_id = s.id
                WHERE dr.admission_id = :id";
        return $this->db->fetchOne($sql, ['id' => $admissionId]);
    }
}
