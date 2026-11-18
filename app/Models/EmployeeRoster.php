<?php

namespace App\Models;

use App\Core\Model;

class EmployeeRoster extends Model
{
    protected string $table = 'employee_rosters';
    protected bool $timestamps = false;
    protected array $fillable = [
        'employee_id',
        'shift_id',
        'department_id',
        'duty_date',
        'duty_role',
        'status',
        'notes',
    ];

    /**
     * Get roster schedule for a given date range and optional department
     */
    public function getRosterRange(string $startDate, string $endDate, ?int $departmentId = null): array
    {
        $params = [
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ];

        $deptClause = '';
        if ($departmentId !== null && $departmentId > 0) {
            $deptClause = ' AND r.department_id = :dept_id';
            $params['dept_id'] = $departmentId;
        }

        $sql = "SELECT r.*, 
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS employee_name,
                       e.employee_code, e.job_title,
                       s.name_ar AS shift_name_ar, s.name_en AS shift_name_en,
                       s.start_time, s.end_time,
                       d.name_ar AS department_name_ar
                FROM `employee_rosters` r
                INNER JOIN `employees` e ON r.employee_id = e.id
                INNER JOIN `shifts` s ON r.shift_id = s.id
                INNER JOIN `departments` d ON r.department_id = d.id
                WHERE r.duty_date BETWEEN :start_date AND :end_date
                {$deptClause}
                ORDER BY r.duty_date ASC, s.start_time ASC, e.first_name ASC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get roster for a single date
     */
    public function getByDate(string $dutyDate, ?int $departmentId = null): array
    {
        return $this->getRosterRange($dutyDate, $dutyDate, $departmentId);
    }

    /**
     * Count employees scheduled on duty today
     */
    public function countOnDutyToday(): int
    {
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(*) as total FROM `employee_rosters` 
                WHERE `duty_date` = :today AND `status` IN ('scheduled', 'attended')";
        $res = $this->db->fetchOne($sql, ['today' => $today]);
        return (int)($res['total'] ?? 0);
    }

    /**
     * Assign or update roster shift for an employee on a specific date
     */
    public function assignShift(int $employeeId, int $shiftId, int $departmentId, string $dutyDate, ?string $dutyRole = null, ?string $notes = null): int|string
    {
        // Check if roster entry exists for employee and date
        $sql = "SELECT id FROM `employee_rosters` WHERE `employee_id` = :emp_id AND `duty_date` = :duty_date LIMIT 1";
        $existing = $this->db->fetchOne($sql, ['emp_id' => $employeeId, 'duty_date' => $dutyDate]);

        if ($existing) {
            $this->update((int)$existing['id'], [
                'shift_id'      => $shiftId,
                'department_id' => $departmentId,
                'duty_role'     => $dutyRole,
                'notes'         => $notes,
            ]);
            return (int)$existing['id'];
        }

        return $this->create([
            'employee_id'   => $employeeId,
            'shift_id'      => $shiftId,
            'department_id' => $departmentId,
            'duty_date'     => $dutyDate,
            'duty_role'     => $dutyRole,
            'status'        => 'scheduled',
            'notes'         => $notes,
        ]);
    }
}
