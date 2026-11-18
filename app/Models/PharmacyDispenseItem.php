<?php

namespace App\Models;

use App\Core\Model;

class PharmacyDispenseItem extends Model
{
    protected string $table = 'pharmacy_dispense_items';
    protected bool $timestamps = false;

    protected array $fillable = [
        'pharmacy_dispense_id',
        'medicine_id',
        'batch_id',
        'quantity',
        'unit_price',
        'total_price',
    ];
}
