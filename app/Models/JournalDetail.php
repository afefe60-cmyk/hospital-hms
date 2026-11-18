<?php

namespace App\Models;

use App\Core\Model;

class JournalDetail extends Model
{
    protected string $table = 'journal_details';
    protected bool $timestamps = false;

    protected array $fillable = [
        'journal_entry_id',
        'account_id',
        'cost_center_id',
        'debit',
        'credit',
    ];
}
