<?php

namespace App\Models;

use App\Core\Model;

class Prescription extends Model
{
    protected string $table = 'prescriptions';
    protected bool $timestamps = false;
    protected array $fillable = [
        'prescription_code',
        'encounter_id',
        'patient_id',
        'doctor_id',
        'status',
        'notes',
        'created_at',
    ];

    public function generatePrescriptionCode(): string
    {
        $year = date('Y');
        $prefix = "RX-{$year}-";

        $sql = "SELECT prescription_code FROM `prescriptions` WHERE `prescription_code` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
        $last = $this->db->fetchColumn($sql, ['prefix' => "{$prefix}%"]);

        if ($last) {
            $numPart = (int)substr($last, strlen($prefix));
            $next = $numPart + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad((string)$next, 6, '0', STR_PAD_LEFT);
    }

    public function items(int $prescriptionId): array
    {
        $sql = "SELECT * FROM `prescription_items` WHERE `prescription_id` = :id ORDER BY `id` ASC";
        return $this->db->fetchAll($sql, ['id' => $prescriptionId]);
    }

    public function detailed(int $prescriptionId): ?array
    {
        $sql = "SELECT p.*,
                       pat.mrn, pat.gender, pat.date_of_birth, pat.phone_primary,
                       CONCAT(pat.first_name, ' ', pat.second_name, ' ', pat.last_name) AS patient_name,
                       TIMESTAMPDIFF(YEAR, pat.date_of_birth, CURDATE()) AS patient_age,
                       doc.degree_title, doc.medical_license_number,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS doctor_name,
                       s.name_ar AS specialty_name,
                       d.name_ar AS department_name
                FROM `prescriptions` p
                INNER JOIN `patients` pat ON p.patient_id = pat.id
                INNER JOIN `doctors` doc ON p.doctor_id = doc.id
                INNER JOIN `employees` e ON doc.employee_id = e.id
                INNER JOIN `specialties` s ON doc.specialty_id = s.id
                INNER JOIN `departments` d ON e.department_id = d.id
                WHERE p.id = :id LIMIT 1";
        return $this->db->fetchOne($sql, ['id' => $prescriptionId]);
    }
}
