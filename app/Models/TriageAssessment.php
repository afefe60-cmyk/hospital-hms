<?php

namespace App\Models;

use App\Core\Model;

class TriageAssessment extends Model
{
    protected string $table = 'triage_assessments';
    protected bool $timestamps = false;

    protected array $fillable = [
        'emergency_case_id',
        'triage_color',
        'gcs_score',
        'airway_status',
        'breathing_status',
        'circulation_status',
        'systolic_bp',
        'diastolic_bp',
        'pulse_rate',
        'respiratory_rate',
        'temperature',
        'oxygen_saturation',
        'pain_score',
        'triage_nurse_id',
        'notes',
        'assessed_at',
        'created_at',
    ];

    public static function colorBadge(string $color): array
    {
        return match($color) {
            'red' => ['label' => 'إنعاش فوري (Red / Resuscitation)', 'badge' => 'bg-danger text-white', 'icon' => 'alert-triangle'],
            'orange' => ['label' => 'حرج عاجل (Orange / Emergent)', 'badge' => 'bg-warning text-dark', 'icon' => 'alert-circle'],
            'yellow' => ['label' => 'عاجل (Yellow / Urgent)', 'badge' => 'bg-info text-dark', 'icon' => 'clock'],
            'green' => ['label' => 'أقل إلحاحاً (Green / Less Urgent)', 'badge' => 'bg-success text-white', 'icon' => 'check-circle'],
            'blue' => ['label' => 'غير عاجل (Blue / Non-Urgent)', 'badge' => 'bg-secondary text-white', 'icon' => 'info'],
            default => ['label' => 'غير مصنف', 'badge' => 'bg-light text-dark', 'icon' => 'help-circle']
        };
    }
}
