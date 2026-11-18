<?php

namespace App\Models;

use App\Core\Model;

class Diagnosis extends Model
{
    protected string $table = 'diagnoses';
    protected bool $timestamps = false;
    protected array $fillable = [
        'encounter_id',
        'diagnosis_catalog_id',
        'type',
        'clinical_notes',
        'diagnosed_by',
        'created_at',
    ];
}
