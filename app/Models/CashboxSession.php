<?php

namespace App\Models;

use App\Core\Model;

class CashboxSession extends Model
{
    protected string $table = 'cashbox_sessions';
    protected bool $timestamps = false;

    protected array $fillable = [
        'session_number',
        'cashbox_id',
        'cashier_user_id',
        'opened_at',
        'opening_balance',
        'closed_at',
        'expected_closing',
        'actual_closing',
        'difference',
        'notes',
        'status',
        'created_at',
    ];

    public function generateSessionNumber(): string
    {
        $prefix = 'SESS-' . date('Ymd') . '-';
        $sql = "SELECT session_number FROM `cashbox_sessions` WHERE `session_number` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
        $last = $this->db->fetchColumn($sql, ['prefix' => "{$prefix}%"]);

        if ($last) {
            $numPart = (int)substr($last, strlen($prefix));
            $next = $numPart + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    public function activeSessionForUser(int $userId): ?array
    {
        $sql = "SELECT s.*, cb.name_ar AS cashbox_name, cb.code AS cashbox_code
                FROM cashbox_sessions s
                INNER JOIN cashboxes cb ON s.cashbox_id = cb.id
                WHERE s.cashier_user_id = :uid AND s.status = 'open'
                LIMIT 1";
        return $this->db->fetchOne($sql, ['uid' => $userId]);
    }

    public function detailed(int $id): ?array
    {
        $sql = "SELECT s.*, cb.name_ar AS cashbox_name, cb.code AS cashbox_code,
                       u.full_name AS cashier_name
                FROM cashbox_sessions s
                INNER JOIN cashboxes cb ON s.cashbox_id = cb.id
                INNER JOIN users u ON s.cashier_user_id = u.id
                WHERE s.id = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    public function sessionPayments(int $sessionId): array
    {
        $sql = "SELECT p.*, inv.invoice_number,
                       CONCAT(pat.first_name, ' ', pat.second_name, ' ', pat.last_name) AS patient_name,
                       pat.mrn
                FROM payments p
                INNER JOIN invoices inv ON p.invoice_id = inv.id
                INNER JOIN patients pat ON inv.patient_id = pat.id
                WHERE p.session_id = :sid
                ORDER BY p.id DESC";
        return $this->db->fetchAll($sql, ['sid' => $sessionId]);
    }
}
