<?php

namespace App\Models;

use App\Core\Model;

class AnesthesiaRecord extends Model
{
    protected string $table = 'anesthesia_records';
    protected bool $timestamps = false;

    protected array $fillable = [
        'surgery_id',
        'anesthesiologist_id',
        'anesthesia_type',
        'asa_physical_status',
        'pre_anesthesia_assessment',
        'airway_assessment',
        'start_time',
        'end_time',
        'agents_used',
        'recovery_score',
        'complications',
        'anesthesia_notes',
        'created_at',
    ];

    public function detailedForSurgery(int $surgeryId): ?array
    {
        $sql = "SELECT ar.*,
                       CONCAT(e.first_name, ' ', e.last_name) AS anesthesiologist_name,
                       d.medical_license_number
                FROM `anesthesia_records` ar
                INNER JOIN `doctors` d ON ar.anesthesiologist_id = d.id
                INNER JOIN `employees` e ON d.employee_id = e.id
                WHERE ar.surgery_id = :sid";

        return $this->db->fetchOne($sql, ['sid' => $surgeryId]);
    }
}
