<?php

namespace App\Models;

use App\Core\Model;

class MedicineCategory extends Model
{
    protected string $table = 'medicine_categories';
    protected bool $timestamps = false;

    protected array $fillable = [
        'name_ar',
        'name_en',
        'description',
        'is_active',
        'created_at',
    ];
}
