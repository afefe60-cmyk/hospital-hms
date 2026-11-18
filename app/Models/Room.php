<?php

namespace App\Models;

use App\Core\Model;

class Room extends Model
{
    protected string $table = 'rooms';
    protected bool $timestamps = false;
    protected array $fillable = [
        'ward_id',
        'room_number',
        'room_type',
        'daily_rate',
    ];

    public function beds(int $roomId): array
    {
        $sql = "SELECT * FROM `beds` WHERE `room_id` = :id ORDER BY `bed_number` ASC";
        return $this->db->fetchAll($sql, ['id' => $roomId]);
    }
}
