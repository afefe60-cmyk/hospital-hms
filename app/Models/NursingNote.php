<?php

namespace App\Models;

use App\Core\Model;

class NursingNote extends Model
{
    protected string $table = 'nursing_notes';
    protected bool $timestamps = false;

    protected array $fillable = [
        'admission_id',
        'nurse_id',
        'note_type',
        'note_content',
        'recorded_at',
        'created_at',
    ];

    public function forAdmission(int $admissionId): array
    {
        $sql = "SELECT nn.*, u.full_name AS nurse_name
                FROM `nursing_notes` nn
                INNER JOIN `users` u ON nn.nurse_id = u.id
                WHERE nn.admission_id = :id
                ORDER BY nn.recorded_at DESC";
        return $this->db->fetchAll($sql, ['id' => $admissionId]);
    }
}
