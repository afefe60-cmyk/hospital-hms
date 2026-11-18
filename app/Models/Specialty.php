<?php

namespace App\Models;

use App\Core\Model;

class Specialty extends Model
{
    protected string $table = 'specialties';
    protected bool $timestamps = false;
    protected array $fillable = [
        'code',
        'name_ar',
        'name_en',
    ];
}
