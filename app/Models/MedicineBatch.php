<?php

namespace App\Models;

use App\Core\Model;

class MedicineBatch extends Model
{
    protected string $table = 'medicine_batches';
    protected bool $timestamps = false;

    protected array $fillable = [
        'medicine_id',
        'warehouse_id',
        'batch_number',
        'expiry_date',
        'cost_price',
        'sale_price',
        'quantity_on_hand',
        'status',
        'created_at',
    ];

    public function detailed(int $batchId): ?array
    {
        $sql = "SELECT b.*, m.commercial_name, m.scientific_name, m.strength, m.dosage_form,
                       w.name_ar AS warehouse_name
                FROM `medicine_batches` b
                INNER JOIN `medicines` m ON b.medicine_id = m.id
                INNER JOIN `warehouses` w ON b.warehouse_id = w.id
                WHERE b.id = :id";
        return $this->db->fetchOne($sql, ['id' => $batchId]);
    }
}
