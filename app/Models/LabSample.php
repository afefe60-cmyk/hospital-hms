<?php

namespace App\Models;

use App\Core\Model;

class LabSample extends Model
{
    protected string $table = 'lab_samples';
    protected bool $timestamps = false;

    protected array $fillable = [
        'lab_order_id',
        'sample_barcode',
        'sample_type',
        'collected_at',
        'collected_by',
        'status',
        'rejection_reason',
        'created_at',
    ];

    public function generateBarcode(): string
    {
        $today = date('Ymd');
        $prefix = "SMP-{$today}-";

        $sql = "SELECT sample_barcode FROM `lab_samples` WHERE `sample_barcode` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
        $last = $this->db->fetchColumn($sql, ['prefix' => "{$prefix}%"]);

        if ($last) {
            $numPart = (int)substr($last, strlen($prefix));
            $next = $numPart + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad((string)$next, 5, '0', STR_PAD_LEFT);
    }
}
