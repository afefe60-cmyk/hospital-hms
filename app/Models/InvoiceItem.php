<?php

namespace App\Models;

use App\Core\Model;

class InvoiceItem extends Model
{
    protected string $table = 'invoice_items';
    protected bool $timestamps = false;

    protected array $fillable = [
        'invoice_id',
        'service_id',
        'item_type',
        'description',
        'quantity',
        'unit_price',
        'total_amount',
        'created_at',
    ];
}
