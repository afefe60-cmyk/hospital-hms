<?php

namespace App\Models;

use App\Core\Model;

class Cashbox extends Model
{
    protected string $table = 'cashboxes';
    protected bool $timestamps = false;

    protected array $fillable = [
        'code',
        'name_ar',
        'name_en',
        'account_id',
        'is_active',
        'created_at',
    ];
}
