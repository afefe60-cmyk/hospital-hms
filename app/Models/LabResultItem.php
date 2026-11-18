<?php

namespace App\Models;

use App\Core\Model;

class LabResultItem extends Model
{
    protected string $table = 'lab_result_items';
    protected bool $timestamps = false;

    protected array $fillable = [
        'lab_result_id',
        'lab_test_id',
        'parameter_name',
        'result_value',
        'unit',
        'reference_range',
        'flag',
    ];
}
