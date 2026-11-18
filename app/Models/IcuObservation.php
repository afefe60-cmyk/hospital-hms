<?php

namespace App\Models;

use App\Core\Model;

class IcuObservation extends Model
{
    protected string $table = 'icu_observations';
    protected bool $timestamps = false;

    protected array $fillable = [
        'icu_stay_id',
        'recorded_at',
        'recorded_by',
        'systolic_bp',
        'diastolic_bp',
        'pulse_rate',
        'respiratory_rate',
        'temperature',
        'oxygen_saturation',
        'gcs_score',
        'pupil_reaction',
        'ventilator_mode',
        'fio2',
        'peep',
        'urine_output_ml',
        'iv_fluids_ml',
        'arterial_blood_gas',
        'clinical_notes',
        'created_at',
    ];

    public function forStay(int $stayId, int $limit = 50): array
    {
        $sql = "SELECT obs.*, u.full_name AS recorder_name
                FROM `icu_observations` obs
                LEFT JOIN `users` u ON obs.recorded_by = u.id
                WHERE obs.icu_stay_id = :stay_id
                ORDER BY obs.recorded_at DESC LIMIT " . (int)$limit;

        return $this->db->fetchAll($sql, ['stay_id' => $stayId]);
    }
}
