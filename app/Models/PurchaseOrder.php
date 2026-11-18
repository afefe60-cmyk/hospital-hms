<?php

namespace App\Models;

use App\Core\Model;

class PurchaseOrder extends Model
{
    protected string $table = 'purchase_orders';
    protected bool $timestamps = false;

    protected array $fillable = [
        'po_number',
        'supplier_id',
        'warehouse_id',
        'order_date',
        'expected_delivery_date',
        'total_amount',
        'status',
        'notes',
        'created_by',
        'created_at',
    ];

    public function generatePoNumber(): string
    {
        $yearMonth = date('Ym');
        $prefix = "PO-{$yearMonth}-";

        $sql = "SELECT po_number FROM `purchase_orders` WHERE `po_number` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
        $last = $this->db->fetchColumn($sql, ['prefix' => "{$prefix}%"]);

        if ($last) {
            $numPart = (int)substr($last, strlen($prefix));
            $next = $numPart + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    public function detailed(int $poId): ?array
    {
        $sql = "SELECT po.*,
                       s.name AS supplier_name, s.phone AS supplier_phone, s.contact_person,
                       w.name_ar AS warehouse_name,
                       u.full_name AS creator_name
                FROM `purchase_orders` po
                INNER JOIN `suppliers` s ON po.supplier_id = s.id
                INNER JOIN `warehouses` w ON po.warehouse_id = w.id
                INNER JOIN `users` u ON po.created_by = u.id
                WHERE po.id = :id";
        return $this->db->fetchOne($sql, ['id' => $poId]);
    }

    public function items(int $poId): array
    {
        $sql = "SELECT poi.*, m.code AS med_code, m.commercial_name, m.scientific_name, m.strength, m.dosage_form, m.unit
                FROM `purchase_order_items` poi
                INNER JOIN `medicines` m ON poi.medicine_id = m.id
                WHERE poi.purchase_order_id = :id";
        return $this->db->fetchAll($sql, ['id' => $poId]);
    }
}
