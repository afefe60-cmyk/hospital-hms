<?php

namespace App\Models;

use App\Core\Model;

class Doctor extends Model
{
    protected string $table = 'doctors';
    protected bool $timestamps = false;
    protected array $fillable = [
        'employee_id',
        'specialty_id',
        'medical_license_number',
        'degree_title',
        'consultation_fee',
        'commission_percentage',
    ];

    public function allDetailed(): array
    {
        $sql = "SELECT doc.*,
                       e.employee_code,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS full_name,
                       e.phone, e.email,
                       d.name_ar AS department_name_ar,
                       s.name_ar AS specialty_name_ar
                FROM `doctors` doc
                INNER JOIN `employees` e ON doc.employee_id = e.id
                INNER JOIN `departments` d ON e.department_id = d.id
                INNER JOIN `specialties` s ON doc.specialty_id = s.id
                WHERE e.status = 'active'
                ORDER BY doc.id DESC";
        return $this->db->fetchAll($sql);
    }
}
