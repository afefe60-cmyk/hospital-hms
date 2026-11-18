<?php

namespace App\Models;

use App\Core\Model;

class PurchaseOrderItem extends Model
{
    protected string $table = 'purchase_order_items';
    protected bool $timestamps = false;

    protected array $fillable = [
        'purchase_order_id',
        'medicine_id',
        'quantity_ordered',
        'quantity_received',
        'unit_cost',
        'total_cost',
    ];
}
