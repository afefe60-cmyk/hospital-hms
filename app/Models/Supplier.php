<?php

namespace App\Models;

use App\Core\Model;

class Supplier extends Model
{
    protected string $table = 'suppliers';
    protected bool $timestamps = false;

    protected array $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'tax_number',
        'status',
        'created_at',
    ];
}
