<?php

namespace App\Models;

use App\Core\Model;

class Queue extends Model
{
    protected string $table = 'queues';
    protected bool $timestamps = false;
    protected array $fillable = [
        'clinic_id',
        'visit_id',
        'queue_number',
        'called_time',
        'status',
        'created_at',
    ];

    public function activeQueueForClinic(int $clinicId): array
    {
        $today = date('Y-m-d');
        $sql = "SELECT q.*,
                       v.visit_number,
                       p.mrn,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name
                FROM `queues` q
                INNER JOIN `visits` v ON q.visit_id = v.id
                INNER JOIN `patients` p ON v.patient_id = p.id
                WHERE q.clinic_id = :clinic AND DATE(q.created_at) = :today
                ORDER BY q.queue_number ASC";
        return $this->db->fetchAll($sql, ['clinic' => $clinicId, 'today' => $today]);
    }

    public function currentlyCalling(int $clinicId): ?array
    {
        $today = date('Y-m-d');
        $sql = "SELECT q.*,
                       p.mrn,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name
                FROM `queues` q
                INNER JOIN `visits` v ON q.visit_id = v.id
                INNER JOIN `patients` p ON v.patient_id = p.id
                WHERE q.clinic_id = :clinic AND q.status = 'called' AND DATE(q.created_at) = :today
                ORDER BY q.called_time DESC LIMIT 1";
        return $this->db->fetchOne($sql, ['clinic' => $clinicId, 'today' => $today]);
    }
}
