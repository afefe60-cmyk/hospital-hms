<?php

namespace App\Models;

use App\Core\Model;

class Employee extends Model
{
    protected string $table = 'employees';
    protected array $fillable = [
        'employee_code',
        'first_name',
        'second_name',
        'last_name',
        'gender',
        'national_id',
        'phone',
        'email',
        'department_id',
        'job_title',
        'hire_date',
        'basic_salary',
        'status',
    ];

    public function allWithDepartment(): array
    {
        $sql = "SELECT e.*, d.name_ar AS department_name_ar
                FROM `employees` e
                INNER JOIN `departments` d ON e.department_id = d.id
                ORDER BY e.id DESC";
        return $this->db->fetchAll($sql);
    }
}
