<?php

namespace App\Models;

use App\Core\Model;

class Department extends Model
{
    protected string $table = 'departments';
    protected array $fillable = [
        'code',
        'name_ar',
        'name_en',
        'type',
        'head_employee_id',
        'cost_center_id',
        'is_active',
    ];

    public function clinics(int $departmentId): array
    {
        $sql = "SELECT * FROM `clinics` WHERE `department_id` = :id AND `is_active` = 1";
        return $this->db->fetchAll($sql, ['id' => $departmentId]);
    }

    public function employees(int $departmentId): array
    {
        $sql = "SELECT * FROM `employees` WHERE `department_id` = :id AND `status` = 'active'";
        return $this->db->fetchAll($sql, ['id' => $departmentId]);
    }
}
