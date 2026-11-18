<?php

namespace App\Models;

use App\Core\Model;

class Medicine extends Model
{
    protected string $table = 'medicines';
    protected bool $timestamps = false;

    protected array $fillable = [
        'category_id',
        'code',
        'scientific_name',
        'commercial_name',
        'dosage_form',
        'strength',
        'unit',
        'minimum_stock_alert',
        'reorder_level',
        'is_prescription_required',
        'is_narcotic',
        'is_active',
        'created_at',
    ];

    public function detailed(int $medicineId): ?array
    {
        $sql = "SELECT m.*, mc.name_ar AS category_name,
                       COALESCE(SUM(b.quantity_on_hand), 0) AS total_stock
                FROM `medicines` m
                INNER JOIN `medicine_categories` mc ON m.category_id = mc.id
                LEFT JOIN `medicine_batches` b ON m.id = b.medicine_id AND b.status = 'active'
                WHERE m.id = :id
                GROUP BY m.id";
        return $this->db->fetchOne($sql, ['id' => $medicineId]);
    }

    public function totalStock(int $medicineId, ?int $warehouseId = null): int
    {
        $params = ['mid' => $medicineId];
        $whereWh = "";
        if ($warehouseId !== null) {
            $whereWh = " AND warehouse_id = :wid";
            $params['wid'] = $warehouseId;
        }

        $sql = "SELECT COALESCE(SUM(quantity_on_hand), 0)
                FROM `medicine_batches`
                WHERE medicine_id = :mid AND status = 'active' {$whereWh}";
        return (int)$this->db->fetchColumn($sql, $params);
    }

    public function activeBatches(int $medicineId, ?int $warehouseId = null): array
    {
        $params = ['mid' => $medicineId];
        $whereWh = "";
        if ($warehouseId !== null) {
            $whereWh = " AND b.warehouse_id = :wid";
            $params['wid'] = $warehouseId;
        }

        // FEFO: First Expired, First Out
        $sql = "SELECT b.*, w.name_ar AS warehouse_name
                FROM `medicine_batches` b
                INNER JOIN `warehouses` w ON b.warehouse_id = w.id
                WHERE b.medicine_id = :mid AND b.status = 'active' AND b.quantity_on_hand > 0 {$whereWh}
                ORDER BY b.expiry_date ASC, b.id ASC";
        return $this->db->fetchAll($sql, $params);
    }
}
