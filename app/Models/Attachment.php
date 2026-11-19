<?php

namespace App\Models;

use App\Core\Model;

class Attachment extends Model
{
    protected string $table = 'attachments';
    protected bool $timestamps = false;
    protected array $fillable = [
        'attachable_type',
        'attachable_id',
        'file_name',
        'file_path',
        'file_mime',
        'file_size_bytes',
        'uploaded_by',
    ];

    /**
     * Get attachments for a specific model instance
     */
    public function forModel(string $type, int $id): array
    {
        $sql = "SELECT a.*, u.full_name AS uploader_name 
                FROM `attachments` a
                LEFT JOIN `users` u ON a.uploaded_by = u.id
                WHERE a.attachable_type = :type AND a.attachable_id = :id
                ORDER BY a.id DESC";

        return $this->db->fetchAll($sql, ['type' => $type, 'id' => $id]);
    }
}
