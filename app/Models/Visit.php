<?php

namespace App\Models;

use App\Core\Model;

class Visit extends Model
{
    protected string $table = 'visits';
    protected array $fillable = [
        'visit_number',
        'patient_id',
        'visit_type',
        'department_id',
        'attending_doctor_id',
        'arrival_time',
        'discharge_time',
        'status',
        'created_by',
    ];

    public function generateVisitNumber(): string
    {
        $todayPrefix = 'VIS-' . date('Ymd') . '-';
        $sql = "SELECT visit_number FROM `visits` WHERE `visit_number` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
        $last = $this->db->fetchColumn($sql, ['prefix' => "{$todayPrefix}%"]);

        if ($last) {
            $numPart = (int)substr($last, strlen($todayPrefix));
            $next = $numPart + 1;
        } else {
            $next = 1;
        }

        return $todayPrefix . str_pad((string)$next, 5, '0', STR_PAD_LEFT);
    }
}
