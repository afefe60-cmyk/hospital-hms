<?php

namespace App\Models;

use App\Core\Model;

class StockMovement extends Model
{
    protected string $table = 'stock_movements';
    protected bool $timestamps = false;

    protected array $fillable = [
        'movement_number',
        'movement_type',
        'medicine_id',
        'batch_id',
        'warehouse_id',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
        'created_at',
    ];

    public function generateMovementNumber(): string
    {
        $today = date('Ymd');
        $prefix = "MOV-{$today}-";

        $sql = "SELECT movement_number FROM `stock_movements` WHERE `movement_number` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
        $last = $this->db->fetchColumn($sql, ['prefix' => "{$prefix}%"]);

        if ($last) {
            $numPart = (int)substr($last, strlen($prefix));
            $next = $numPart + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad((string)$next, 5, '0', STR_PAD_LEFT);
    }

    public static function log(
        string $type,
        int $medicineId,
        int $batchId,
        int $warehouseId,
        int $qty,
        ?string $refType = null,
        ?int $refId = null,
        ?string $notes = null
    ): int {
        $model = new self();
        $movNum = $model->generateMovementNumber();
        return (int)$model->create([
            'movement_number' => $movNum,
            'movement_type' => $type,
            'medicine_id' => $medicineId,
            'batch_id' => $batchId,
            'warehouse_id' => $warehouseId,
            'quantity' => $qty,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'notes' => $notes,
            'created_by' => auth()->id() ?? 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
