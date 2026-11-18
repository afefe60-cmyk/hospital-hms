<?php

namespace App\Models;

use App\Core\Model;

class VitalSign extends Model
{
    protected string $table = 'vital_signs';
    protected bool $timestamps = false;
    protected array $fillable = [
        'encounter_id',
        'visit_id',
        'systolic_bp',
        'diastolic_bp',
        'pulse_rate',
        'respiratory_rate',
        'temperature',
        'oxygen_saturation',
        'random_blood_sugar',
        'weight_kg',
        'height_cm',
        'bmi',
        'recorded_by',
        'recorded_at',
    ];

    public static function calculateBmi(?float $weightKg, ?float $heightCm): ?float
    {
        if (!$weightKg || !$heightCm || $heightCm <= 0) {
            return null;
        }

        $heightM = $heightCm / 100.0;
        $bmi = $weightKg / ($heightM * $heightM);
        return round($bmi, 1);
    }
}
