<?php

namespace App\Models;

use App\Core\Model;

class Account extends Model
{
    protected string $table = 'accounts';
    protected bool $timestamps = false;

    protected array $fillable = [
        'account_code',
        'account_name_ar',
        'account_name_en',
        'parent_id',
        'account_type',
        'is_transactional',
        'current_balance',
        'created_at',
    ];

    public function tree(): array
    {
        $all = $this->db->fetchAll("SELECT * FROM accounts ORDER BY account_code ASC");
        return $all;
    }
}
