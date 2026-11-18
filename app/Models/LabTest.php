<?php

namespace App\Models;

use App\Core\Model;

class LabTest extends Model
{
    protected string $table = 'lab_tests';
    protected bool $timestamps = false;

    protected array $fillable = [
        'code',
        'name_en',
        'name_ar',
        'category',
        'sample_type',
        'price',
        'normal_range',
        'unit',
        'turnaround_time_hours',
        'is_active',
        'created_at',
    ];
}
