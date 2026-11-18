<?php

namespace App\Models;

use App\Core\Model;

class EmployeeLeave extends Model
{
    protected string $table = 'employee_leaves';
    protected bool $timestamps = false;
    protected array $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'approved_by',
    ];

    /**
     * Get all leaves with employee and approver details
     */
    public function allDetailed(?string $status = null, ?int $employeeId = null): array
    {
        $params = [];
        $where = [];

        if (!empty($status)) {
            $where[] = "l.status = :status";
            $params['status'] = $status;
        }

        if (!empty($employeeId)) {
            $where[] = "l.employee_id = :employee_id";
            $params['employee_id'] = $employeeId;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT l.*,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS employee_name,
                       e.employee_code, e.job_title,
                       d.name_ar AS department_name_ar,
                       u.full_name AS approver_name
                FROM `employee_leaves` l
                INNER JOIN `employees` e ON l.employee_id = e.id
                INNER JOIN `departments` d ON e.department_id = d.id
                LEFT JOIN `users` u ON l.approved_by = u.id
                {$whereClause}
                ORDER BY l.id DESC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get leave by ID with details
     */
    public function findDetailed(int $id): ?array
    {
        $sql = "SELECT l.*,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS employee_name,
                       e.employee_code, e.job_title, e.phone,
                       d.name_ar AS department_name_ar,
                       u.full_name AS approver_name
                FROM `employee_leaves` l
                INNER JOIN `employees` e ON l.employee_id = e.id
                INNER JOIN `departments` d ON e.department_id = d.id
                LEFT JOIN `users` u ON l.approved_by = u.id
                WHERE l.id = :id
                LIMIT 1";

        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    /**
     * Count employees currently on leave today
     */
    public function countOnLeaveToday(): int
    {
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(*) as total FROM `employee_leaves` 
                WHERE `status` = 'approved' AND :today BETWEEN `start_date` AND `end_date`";
        $res = $this->db->fetchOne($sql, ['today' => $today]);
        return (int)($res['total'] ?? 0);
    }
}
