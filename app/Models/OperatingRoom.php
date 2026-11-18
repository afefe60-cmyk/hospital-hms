<?php

namespace App\Models;

use App\Core\Model;

class OperatingRoom extends Model
{
    protected string $table = 'operating_rooms';
    protected bool $timestamps = false;

    protected array $fillable = [
        'name_ar',
        'name_en',
        'status',
        'created_at',
    ];
}
