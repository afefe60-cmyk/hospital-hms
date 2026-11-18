<?php

namespace App\Models;

use App\Core\Model;

class Clinic extends Model
{
    protected string $table = 'clinics';
    protected bool $timestamps = false;
    protected array $fillable = [
        'department_id',
        'room_number',
        'name_ar',
        'name_en',
        'is_active',
    ];

    public function allWithDepartment(): array
    {
        $sql = "SELECT c.*, d.name_ar AS department_name_ar, d.code AS department_code
                FROM `clinics` c
                INNER JOIN `departments` d ON c.department_id = d.id
                ORDER BY c.id DESC";
        return $this->db->fetchAll($sql);
    }
}
