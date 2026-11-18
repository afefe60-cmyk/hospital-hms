<?php

namespace App\Models;

use App\Core\Model;

class Bed extends Model
{
    protected string $table = 'beds';
    protected bool $timestamps = false;
    protected array $fillable = [
        'room_id',
        'bed_number',
        'status',
        'is_active',
    ];

    public function matrix(): array
    {
        $sql = "SELECT b.id AS bed_id, b.bed_number, b.status AS bed_status,
                       r.id AS room_id, r.room_number, r.room_type, r.daily_rate,
                       w.id AS ward_id, w.name_ar AS ward_name_ar, w.floor_number, w.gender_type,
                       d.name_ar AS department_name_ar
                FROM `beds` b
                INNER JOIN `rooms` r ON b.room_id = r.id
                INNER JOIN `wards` w ON r.ward_id = w.id
                INNER JOIN `departments` d ON w.department_id = d.id
                WHERE b.is_active = 1
                ORDER BY w.id ASC, r.room_number ASC, b.bed_number ASC";
        $rows = $this->db->fetchAll($sql);

        // Group by Ward -> Room -> Beds
        $matrix = [];
        foreach ($rows as $row) {
            $wardId = $row['ward_id'];
            $roomId = $row['room_id'];

            if (!isset($matrix[$wardId])) {
                $matrix[$wardId] = [
                    'ward_id' => $wardId,
                    'ward_name' => $row['ward_name_ar'],
                    'floor' => $row['floor_number'],
                    'gender' => $row['gender_type'],
                    'department' => $row['department_name_ar'],
                    'rooms' => [],
                ];
            }

            if (!isset($matrix[$wardId]['rooms'][$roomId])) {
                $matrix[$wardId]['rooms'][$roomId] = [
                    'room_id' => $roomId,
                    'room_number' => $row['room_number'],
                    'room_type' => $row['room_type'],
                    'daily_rate' => $row['daily_rate'],
                    'beds' => [],
                ];
            }

            $matrix[$wardId]['rooms'][$roomId]['beds'][] = [
                'bed_id' => $row['bed_id'],
                'bed_number' => $row['bed_number'],
                'status' => $row['bed_status'],
            ];
        }

        return $matrix;
    }

    public function countsByStatus(): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM `beds` WHERE `is_active` = 1 GROUP BY `status`";
        $results = $this->db->fetchAll($sql);
        $counts = [
            'total' => 0,
            'available' => 0,
            'occupied' => 0,
            'cleaning' => 0,
            'maintenance' => 0,
            'reserved' => 0,
        ];

        foreach ($results as $r) {
            $counts[$r['status']] = (int)$r['count'];
            $counts['total'] += (int)$r['count'];
        }

        return $counts;
    }

    public function updateStatus(int $bedId, string $status): bool
    {
        $validStatuses = ['available', 'occupied', 'cleaning', 'maintenance', 'reserved'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        return $this->update($bedId, ['status' => $status]);
    }
}
