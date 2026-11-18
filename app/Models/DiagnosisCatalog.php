<?php

namespace App\Models;

use App\Core\Model;

class DiagnosisCatalog extends Model
{
    protected string $table = 'diagnosis_catalog';
    protected bool $timestamps = false;
    protected array $fillable = [
        'icd10_code',
        'description_en',
        'description_ar',
        'is_infectious',
    ];

    public function search(string $term): array
    {
        $sql = "SELECT * FROM `diagnosis_catalog`
                WHERE `icd10_code` LIKE :term OR `description_ar` LIKE :term OR `description_en` LIKE :term
                ORDER BY `icd10_code` ASC LIMIT 30";
        return $this->db->fetchAll($sql, ['term' => "%{$term}%"]);
    }
}
