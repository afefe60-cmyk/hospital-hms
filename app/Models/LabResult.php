<?php

namespace App\Models;

use App\Core\Model;

class LabResult extends Model
{
    protected string $table = 'lab_results';
    protected bool $timestamps = false;

    protected array $fillable = [
        'lab_order_id',
        'verified_by',
        'verified_at',
        'status',
        'general_comments',
        'created_at',
    ];

    public function items(int $resultId): array
    {
        $sql = "SELECT lri.*, lt.code AS test_code, lt.name_ar AS test_name_ar, lt.name_en AS test_name_en, lt.category
                FROM `lab_result_items` lri
                INNER JOIN `lab_tests` lt ON lri.lab_test_id = lt.id
                WHERE lri.lab_result_id = :id
                ORDER BY lt.category ASC, lri.id ASC";
        return $this->db->fetchAll($sql, ['id' => $resultId]);
    }
}
