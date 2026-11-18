<?php

namespace App\Models;

use App\Core\Model;

class RadiologyService extends Model
{
    protected string $table = 'radiology_services';
    protected bool $timestamps = false;

    protected array $fillable = [
        'code',
        'name_en',
        'name_ar',
        'modality',
        'price',
        'preparation_instructions',
        'is_active',
        'created_at',
    ];
}
