<?php

namespace App\Models;

use App\Core\Model;

class CostCenter extends Model
{
    protected string $table = 'cost_centers';
    protected bool $timestamps = false;

    protected array $fillable = [
        'code',
        'name_ar',
        'name_en',
        'created_at',
    ];
}
