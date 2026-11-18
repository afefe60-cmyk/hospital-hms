<?php

namespace App\Models;

use App\Core\Model;

class BedAssignment extends Model
{
    protected string $table = 'bed_assignments';
    protected bool $timestamps = false;

    protected array $fillable = [
        'admission_id',
        'bed_id',
        'assigned_from',
        'assigned_to',
        'is_active',
        'notes',
        'created_at',
    ];

    public function assignBed(int $admissionId, int $bedId, ?string $notes = null): int
    {
        $now = date('Y-m-d H:i:s');

        // Check if there was a previous active bed assignment
        $previous = $this->db->fetchOne("SELECT * FROM `bed_assignments` WHERE `admission_id` = :aid AND `is_active` = 1 LIMIT 1", ['aid' => $admissionId]);
        if ($previous) {
            $this->update($previous['id'], [
                'is_active' => 0,
                'assigned_to' => $now,
            ]);
            // Put old bed into cleaning
            $this->db->query("UPDATE `beds` SET `status` = 'cleaning' WHERE `id` = :id", ['id' => $previous['bed_id']]);
        }

        // Create new assignment
        $assignId = $this->create([
            'admission_id' => $admissionId,
            'bed_id' => $bedId,
            'assigned_from' => $now,
            'is_active' => 1,
            'notes' => $notes,
            'created_at' => $now,
        ]);

        // Update bed status to occupied
        $this->db->query("UPDATE `beds` SET `status` = 'occupied' WHERE `id` = :id", ['id' => $bedId]);

        return $assignId;
    }

    public function releaseBed(int $admissionId): void
    {
        $now = date('Y-m-d H:i:s');
        $active = $this->db->fetchOne("SELECT * FROM `bed_assignments` WHERE `admission_id` = :aid AND `is_active` = 1 LIMIT 1", ['aid' => $admissionId]);
        if ($active) {
            $this->update($active['id'], [
                'is_active' => 0,
                'assigned_to' => $now,
            ]);
            // Set bed to cleaning
            $this->db->query("UPDATE `beds` SET `status` = 'cleaning' WHERE `id` = :id", ['id' => $active['bed_id']]);
        }
    }
}
