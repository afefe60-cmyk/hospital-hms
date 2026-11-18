<?php

namespace App\Models;

use App\Core\Model;

class PatientAllergy extends Model
{
    protected string $table = 'patient_allergies';
    protected bool $timestamps = false;
    protected array $fillable = [
        'patient_id',
        'allergen_type',
        'allergen_name',
        'severity',
        'reaction_notes',
        'recorded_by',
    ];
}
