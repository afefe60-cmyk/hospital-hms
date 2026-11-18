<?php

namespace App\Models;

use App\Core\Model;

class Ward extends Model
{
    protected string $table = 'wards';
    protected bool $timestamps = false;
    protected array $fillable = [
        'department_id',
        'name_ar',
        'name_en',
        'gender_type',
        'floor_number',
        'is_active',
    ];

    public function rooms(int $wardId): array
    {
        $sql = "SELECT * FROM `rooms` WHERE `ward_id` = :id ORDER BY `room_number` ASC";
        return $this->db->fetchAll($sql, ['id' => $wardId]);
    }
}
