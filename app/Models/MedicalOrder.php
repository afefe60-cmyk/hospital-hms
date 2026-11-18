<?php

namespace App\Models;

use App\Core\Model;

class MedicalOrder extends Model
{
    protected string $table = 'medical_orders';
    protected bool $timestamps = false;
    protected array $fillable = [
        'encounter_id',
        'visit_id',
        'order_type',
        'order_description',
        'priority',
        'ordering_doctor_id',
        'status',
        'clinical_indication',
        'created_at',
    ];
}
