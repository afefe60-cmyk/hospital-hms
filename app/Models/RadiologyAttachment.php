<?php

namespace App\Models;

use App\Core\Model;

class RadiologyAttachment extends Model
{
    protected string $table = 'radiology_attachments';
    protected bool $timestamps = false;

    protected array $fillable = [
        'radiology_order_id',
        'file_name',
        'file_path',
        'file_type',
        'uploaded_at',
    ];
}
