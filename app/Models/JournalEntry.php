<?php

namespace App\Models;

use App\Core\Model;

class JournalEntry extends Model
{
    protected string $table = 'journal_entries';
    protected bool $timestamps = false;

    protected array $fillable = [
        'entry_number',
        'entry_date',
        'description',
        'source_document',
        'is_posted',
        'posted_by',
        'created_at',
    ];

    public function generateEntryNumber(): string
    {
        $prefix = 'JV-' . date('Ym') . '-';
        $sql = "SELECT entry_number FROM `journal_entries` WHERE `entry_number` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
        $last = $this->db->fetchColumn($sql, ['prefix' => "{$prefix}%"]);

        if ($last) {
            $numPart = (int)substr($last, strlen($prefix));
            $next = $numPart + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad((string)$next, 5, '0', STR_PAD_LEFT);
    }

    public function details(int $entryId): array
    {
        $sql = "SELECT jd.*, a.account_code, a.account_name_ar, a.account_name_en, cc.name_ar AS cost_center_name
                FROM journal_details jd
                INNER JOIN accounts a ON jd.account_id = a.id
                LEFT JOIN cost_centers cc ON jd.cost_center_id = cc.id
                WHERE jd.journal_entry_id = :id";
        return $this->db->fetchAll($sql, ['id' => $entryId]);
    }
}
