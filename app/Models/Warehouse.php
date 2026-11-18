<?php

namespace App\Models;

use App\Core\Model;

class Warehouse extends Model
{
    protected string $table = 'warehouses';
    protected bool $timestamps = false;

    protected array $fillable = [
        'code',
        'name_ar',
        'name_en',
        'type',
        'location',
        'is_active',
        'created_at',
    ];
}
