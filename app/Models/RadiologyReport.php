<?php

namespace App\Models;

use App\Core\Model;

class RadiologyReport extends Model
{
    protected string $table = 'radiology_reports';
    protected bool $timestamps = false;

    protected array $fillable = [
        'radiology_order_id',
        'radiologist_id',
        'findings',
        'impression',
        'reported_at',
        'status',
        'created_at',
    ];
}
