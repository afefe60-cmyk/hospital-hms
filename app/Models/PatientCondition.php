<?php

namespace App\Models;

use App\Core\Model;

class PatientCondition extends Model
{
    protected string $table = 'patient_conditions';
    protected bool $timestamps = false;
    protected array $fillable = [
        'patient_id',
        'condition_name',
        'icd10_code',
        'diagnosed_date',
        'is_active',
    ];
}
