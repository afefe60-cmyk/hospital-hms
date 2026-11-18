<?php

namespace App\Models;

use App\Core\Model;

class Payment extends Model
{
    protected string $table = 'payments';
    protected bool $timestamps = false;

    protected array $fillable = [
        'receipt_number',
        'invoice_id',
        'session_id',
        'amount',
        'payment_method',
        'transaction_reference',
        'notes',
        'collected_by',
        'created_at',
    ];

    public function generateReceiptNumber(): string
    {
        $prefix = 'RCP-' . date('Ym') . '-';
        $sql = "SELECT receipt_number FROM `payments` WHERE `receipt_number` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
        $last = $this->db->fetchColumn($sql, ['prefix' => "{$prefix}%"]);

        if ($last) {
            $numPart = (int)substr($last, strlen($prefix));
            $next = $numPart + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad((string)$next, 5, '0', STR_PAD_LEFT);
    }

    public function detailed(int $id): ?array
    {
        $sql = "SELECT p.*,
                       inv.invoice_number, inv.total_patient_due, inv.paid_amount AS total_paid_so_far,
                       pat.mrn, pat.phone_primary,
                       CONCAT(pat.first_name, ' ', pat.second_name, ' ', pat.last_name) AS patient_name,
                       u.full_name AS cashier_name,
                       cs.session_number
                FROM payments p
                INNER JOIN invoices inv ON p.invoice_id = inv.id
                INNER JOIN patients pat ON inv.patient_id = pat.id
                LEFT JOIN users u ON p.collected_by = u.id
                LEFT JOIN cashbox_sessions cs ON p.session_id = cs.id
                WHERE p.id = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }
}
