<?php

namespace App\Models;

use App\Core\Model;

class Admission extends Model
{
    protected string $table = 'admissions';
    protected bool $timestamps = false;

    protected array $fillable = [
        'admission_number',
        'patient_id',
        'admitting_doctor_id',
        'department_id',
        'emergency_case_id',
        'admission_date',
        'discharge_date',
        'admission_type',
        'admission_reason',
        'provisional_diagnosis',
        'status',
        'created_at',
    ];

    public function generateAdmissionNumber(): string
    {
        $year = date('Y');
        $prefix = "ADM-{$year}-";

        $sql = "SELECT admission_number FROM `admissions` WHERE `admission_number` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
        $last = $this->db->fetchColumn($sql, ['prefix' => "{$prefix}%"]);

        if ($last) {
            $numPart = (int)substr($last, strlen($prefix));
            $next = $numPart + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad((string)$next, 5, '0', STR_PAD_LEFT);
    }

    public function detailed(int $id): ?array
    {
        $sql = "SELECT a.*,
                       p.mrn, p.gender, p.date_of_birth, p.blood_group, p.phone_primary,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS doctor_name,
                       s.name_ar AS doctor_specialty,
                       d.name_ar AS department_name,
                       b.bed_number,
                       r.room_number, r.room_type, r.daily_rate,
                       w.name_ar AS ward_name,
                       ba.id AS bed_assignment_id,
                       ba.assigned_from AS bed_assigned_from
                FROM `admissions` a
                INNER JOIN `patients` p ON a.patient_id = p.id
                INNER JOIN `doctors` doc ON a.admitting_doctor_id = doc.id
                INNER JOIN `employees` e ON doc.employee_id = e.id
                INNER JOIN `specialties` s ON doc.specialty_id = s.id
                INNER JOIN `departments` d ON a.department_id = d.id
                LEFT JOIN `bed_assignments` ba ON a.id = ba.admission_id AND ba.is_active = 1
                LEFT JOIN `beds` b ON ba.bed_id = b.id
                LEFT JOIN `rooms` r ON b.room_id = r.id
                LEFT JOIN `wards` w ON r.ward_id = w.id
                WHERE a.id = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    public function activeBed(int $admissionId): ?array
    {
        $sql = "SELECT ba.*, b.bed_number,
                       r.room_number, r.room_type, r.daily_rate,
                       w.name_ar AS ward_name
                FROM `bed_assignments` ba
                INNER JOIN `beds` b ON ba.bed_id = b.id
                INNER JOIN `rooms` r ON b.room_id = r.id
                INNER JOIN `wards` w ON r.ward_id = w.id
                WHERE ba.admission_id = :id AND ba.is_active = 1
                LIMIT 1";
        return $this->db->fetchOne($sql, ['id' => $admissionId]);
    }
}
