<?php

namespace App\Models;

use App\Core\Model;

class Surgery extends Model
{
    protected string $table = 'surgeries';
    protected bool $timestamps = true;

    protected array $fillable = [
        'surgery_number',
        'patient_id',
        'admission_id',
        'operating_room_id',
        'lead_surgeon_id',
        'assistant_surgeon_id',
        'anesthesiologist_id',
        'scrub_nurse_id',
        'surgery_type',
        'procedure_name',
        'scheduled_start',
        'scheduled_end',
        'actual_start',
        'actual_end',
        'status',
        'pre_op_diagnosis',
        'post_op_diagnosis',
        'surgery_notes',
        'created_by',
        'created_at',
        'updated_at',
    ];

    public function generateSurgeryNumber(): string
    {
        $prefix = 'SUR-' . date('Ymd') . '-';
        $sql = "SELECT surgery_number FROM `surgeries` WHERE `surgery_number` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
        $last = $this->db->fetchColumn($sql, ['prefix' => "{$prefix}%"]);

        if ($last) {
            $numPart = (int)substr($last, strlen($prefix));
            $next = $numPart + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    public function checkOrConflict(int $operatingRoomId, string $start, string $end, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM `surgeries`
                WHERE `operating_room_id` = :or_id
                AND `status` NOT IN ('cancelled', 'postponed', 'completed')
                AND ((`scheduled_start` < :end_time AND `scheduled_end` > :start_time))";
        
        $params = [
            'or_id' => $operatingRoomId,
            'start_time' => $start,
            'end_time' => $end,
        ];

        if ($excludeId !== null) {
            $sql .= " AND `id` != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        return ((int)$this->db->fetchColumn($sql, $params)) > 0;
    }

    public function detailed(int $id): ?array
    {
        $sql = "SELECT s.*,
                       p.mrn, p.gender AS patient_gender, p.date_of_birth, p.blood_group, p.phone_primary,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                       op.name_ar AS or_name_ar, op.name_en AS or_name_en,
                       adm.admission_number,
                       CONCAT(ed.first_name, ' ', ed.last_name) AS lead_surgeon_name,
                       CONCAT(ea.first_name, ' ', ea.last_name) AS assistant_surgeon_name,
                       CONCAT(ean.first_name, ' ', ean.last_name) AS anesthesiologist_name,
                       CONCAT(en.first_name, ' ', en.last_name) AS scrub_nurse_name,
                       u.full_name AS created_by_name
                FROM `surgeries` s
                INNER JOIN `patients` p ON s.patient_id = p.id
                INNER JOIN `operating_rooms` op ON s.operating_room_id = op.id
                LEFT JOIN `admissions` adm ON s.admission_id = adm.id
                INNER JOIN `doctors` doc ON s.lead_surgeon_id = doc.id
                INNER JOIN `employees` ed ON doc.employee_id = ed.id
                LEFT JOIN `doctors` adoc ON s.assistant_surgeon_id = adoc.id
                LEFT JOIN `employees` ea ON adoc.employee_id = ea.id
                LEFT JOIN `doctors` andoc ON s.anesthesiologist_id = andoc.id
                LEFT JOIN `employees` ean ON andoc.employee_id = ean.id
                LEFT JOIN `employees` en ON s.scrub_nurse_id = en.id
                LEFT JOIN `users` u ON s.created_by = u.id
                WHERE s.id = :id";

        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    public function listWithDetails(?string $status = null, ?string $date = null, int $limit = 50): array
    {
        $sql = "SELECT s.*,
                       p.mrn,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       op.name_ar AS or_name_ar, op.name_en AS or_name_en,
                       CONCAT(ed.first_name, ' ', ed.last_name) AS lead_surgeon_name,
                       CONCAT(ean.first_name, ' ', ean.last_name) AS anesthesiologist_name
                FROM `surgeries` s
                INNER JOIN `patients` p ON s.patient_id = p.id
                INNER JOIN `operating_rooms` op ON s.operating_room_id = op.id
                INNER JOIN `doctors` doc ON s.lead_surgeon_id = doc.id
                INNER JOIN `employees` ed ON doc.employee_id = ed.id
                LEFT JOIN `doctors` andoc ON s.anesthesiologist_id = andoc.id
                LEFT JOIN `employees` ean ON andoc.employee_id = ean.id
                WHERE 1=1";

        $params = [];

        if ($status) {
            $sql .= " AND s.status = :status";
            $params['status'] = $status;
        }

        if ($date) {
            $sql .= " AND DATE(s.scheduled_start) = :sch_date";
            $params['sch_date'] = $date;
        }

        $sql .= " ORDER BY s.scheduled_start ASC LIMIT " . (int)$limit;

        return $this->db->fetchAll($sql, $params);
    }
}
