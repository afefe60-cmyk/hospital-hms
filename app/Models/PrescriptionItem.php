<?php

namespace App\Models;

use App\Core\Model;

class PrescriptionItem extends Model
{
    protected string $table = 'prescription_items';
    protected bool $timestamps = false;
    protected array $fillable = [
        'prescription_id',
        'medicine_name',
        'dosage',
        'frequency',
        'duration_days',
        'total_quantity',
        'instructions',
        'is_dispensed',
    ];
}
