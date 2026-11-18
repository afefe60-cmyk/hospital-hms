<?php

namespace App\Models;

use App\Core\Model;

class EmergencyCase extends Model
{
    protected string $table = 'emergency_cases';
    protected bool $timestamps = false;

    protected array $fillable = [
        'case_number',
        'patient_id',
        'temporary_patient_name',
        'arrival_mode',
        'arrival_time',
        'triage_level',
        'chief_complaint',
        'attending_doctor_id',
        'status',
        'outcome_notes',
        'created_at',
    ];

    public function generateCaseNumber(): string
    {
        $today = date('Ymd');
        $prefix = "ER-{$today}-";

        $sql = "SELECT case_number FROM `emergency_cases` WHERE `case_number` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
        $last = $this->db->fetchColumn($sql, ['prefix' => "{$prefix}%"]);

        if ($last) {
            $numPart = (int)substr($last, strlen($prefix));
            $next = $numPart + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    public function detailed(int $id): ?array
    {
        $sql = "SELECT er.*,
                       p.mrn, p.gender, p.date_of_birth, p.blood_group, p.phone_primary,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS registered_patient_name,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS doctor_name,
                       s.name_ar AS doctor_specialty
                FROM `emergency_cases` er
                LEFT JOIN `patients` p ON er.patient_id = p.id
                LEFT JOIN `doctors` d ON er.attending_doctor_id = d.id
                LEFT JOIN `employees` e ON d.employee_id = e.id
                LEFT JOIN `specialties` s ON d.specialty_id = s.id
                WHERE er.id = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    public function triageAssessment(int $id): ?array
    {
        $sql = "SELECT ta.*, u.full_name AS nurse_name
                FROM `triage_assessments` ta
                INNER JOIN `users` u ON ta.triage_nurse_id = u.id
                WHERE ta.emergency_case_id = :id
                ORDER BY ta.id DESC LIMIT 1";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }
}
