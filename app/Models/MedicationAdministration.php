<?php

namespace App\Models;

use App\Core\Model;

class MedicationAdministration extends Model
{
    protected string $table = 'medication_administrations';
    protected bool $timestamps = false;

    protected array $fillable = [
        'admission_id',
        'medicine_name',
        'dosage',
        'route',
        'scheduled_time',
        'administered_time',
        'administered_by',
        'status',
        'notes',
        'created_at',
    ];

    public function forAdmission(int $admissionId): array
    {
        $sql = "SELECT m.*, u.full_name AS nurse_name
                FROM `medication_administrations` m
                LEFT JOIN `users` u ON m.administered_by = u.id
                WHERE m.admission_id = :id
                ORDER BY m.scheduled_time ASC, m.id ASC";
        return $this->db->fetchAll($sql, ['id' => $admissionId]);
    }
}
