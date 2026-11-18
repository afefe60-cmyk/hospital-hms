<?php

namespace App\Models;

use App\Core\Model;

class Invoice extends Model
{
    protected string $table = 'invoices';
    protected bool $timestamps = true;

    protected array $fillable = [
        'invoice_number',
        'visit_id',
        'patient_id',
        'admission_id',
        'total_gross',
        'total_discount',
        'total_insurance',
        'total_patient_due',
        'paid_amount',
        'status',
        'created_by',
        'notes',
        'created_at',
        'updated_at',
    ];

    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . date('Ym') . '-';
        $sql = "SELECT invoice_number FROM `invoices` WHERE `invoice_number` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
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
        $sql = "SELECT inv.*,
                       p.mrn, p.gender AS patient_gender, p.date_of_birth, p.blood_group, p.phone_primary,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                       adm.admission_number,
                       u.full_name AS creator_name
                FROM `invoices` inv
                INNER JOIN `patients` p ON inv.patient_id = p.id
                LEFT JOIN `admissions` adm ON inv.admission_id = adm.id
                LEFT JOIN `users` u ON inv.created_by = u.id
                WHERE inv.id = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    public function items(int $invoiceId): array
    {
        $sql = "SELECT it.*, s.code AS service_code
                FROM invoice_items it
                LEFT JOIN service_catalog s ON it.service_id = s.id
                WHERE it.invoice_id = :id
                ORDER BY it.id ASC";
        return $this->db->fetchAll($sql, ['id' => $invoiceId]);
    }

    public function payments(int $invoiceId): array
    {
        $sql = "SELECT p.*, u.full_name AS collector_name
                FROM payments p
                LEFT JOIN users u ON p.collected_by = u.id
                WHERE p.invoice_id = :id
                ORDER BY p.id ASC";
        return $this->db->fetchAll($sql, ['id' => $invoiceId]);
    }

    public function listWithDetails(?string $status = null, ?int $patientId = null, int $limit = 50): array
    {
        $sql = "SELECT inv.*,
                       p.mrn,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       p.phone_primary,
                       u.full_name AS creator_name
                FROM invoices inv
                INNER JOIN patients p ON inv.patient_id = p.id
                LEFT JOIN users u ON inv.created_by = u.id
                WHERE 1=1";
        $params = [];

        if ($status) {
            $sql .= " AND inv.status = :status";
            $params['status'] = $status;
        }

        if ($patientId) {
            $sql .= " AND inv.patient_id = :pid";
            $params['pid'] = $patientId;
        }

        $sql .= " ORDER BY inv.id DESC LIMIT " . (int)$limit;
        return $this->db->fetchAll($sql, $params);
    }

    public function recalculateTotals(int $invoiceId): void
    {
        $gross = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(total_amount), 0) FROM invoice_items WHERE invoice_id = :id", ['id' => $invoiceId]);
        $paid = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE invoice_id = :id", ['id' => $invoiceId]);

        $invoice = $this->find($invoiceId);
        $discount = (float)($invoice['total_discount'] ?? 0);
        $insurance = (float)($invoice['total_insurance'] ?? 0);

        $patientDue = max(0.00, $gross - $discount - $insurance);

        $status = 'issued';
        if ($paid >= $patientDue && $patientDue > 0) {
            $status = 'paid';
        } elseif ($paid > 0 && $paid < $patientDue) {
            $status = 'partially_paid';
        }

        $this->update($invoiceId, [
            'total_gross' => $gross,
            'total_patient_due' => $patientDue,
            'paid_amount' => $paid,
            'status' => $status,
        ]);
    }
}
