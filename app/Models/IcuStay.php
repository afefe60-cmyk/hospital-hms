<?php

namespace App\Models;

use App\Core\Model;

class IcuStay extends Model
{
    protected string $table = 'icu_stays';
    protected bool $timestamps = true;

    protected array $fillable = [
        'stay_number',
        'patient_id',
        'admission_id',
        'bed_id',
        'attending_intensivist_id',
        'admitted_at',
        'discharged_at',
        'admission_reason',
        'apache_score',
        'ventilator_status',
        'status',
        'discharge_summary',
        'created_at',
        'updated_at',
    ];

    public function generateStayNumber(): string
    {
        $prefix = 'ICU-' . date('Ymd') . '-';
        $sql = "SELECT stay_number FROM `icu_stays` WHERE `stay_number` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
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
        $sql = "SELECT s.*,
                       p.mrn, p.gender AS patient_gender, p.date_of_birth, p.blood_group, p.phone_primary,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                       b.bed_number, r.room_number, w.name_ar AS ward_name_ar, w.name_en AS ward_name_en,
                       adm.admission_number,
                       CONCAT(ed.first_name, ' ', ed.last_name) AS doctor_name,
                       ed.phone AS doctor_phone
                FROM `icu_stays` s
                INNER JOIN `patients` p ON s.patient_id = p.id
                INNER JOIN `beds` b ON s.bed_id = b.id
                INNER JOIN `rooms` r ON b.room_id = r.id
                INNER JOIN `wards` w ON r.ward_id = w.id
                LEFT JOIN `admissions` adm ON s.admission_id = adm.id
                LEFT JOIN `doctors` doc ON s.attending_intensivist_id = doc.id
                LEFT JOIN `employees` ed ON doc.employee_id = ed.id
                WHERE s.id = :id";

        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    public function listWithDetails(?string $status = null, int $limit = 50): array
    {
        $sql = "SELECT s.*,
                       p.mrn,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                       p.gender AS patient_gender,
                       b.bed_number, r.room_number,
                       CONCAT(ed.first_name, ' ', ed.last_name) AS doctor_name,
                       (SELECT recorded_at FROM `icu_observations` WHERE icu_stay_id = s.id ORDER BY recorded_at DESC LIMIT 1) AS last_observation_time
                FROM `icu_stays` s
                INNER JOIN `patients` p ON s.patient_id = p.id
                INNER JOIN `beds` b ON s.bed_id = b.id
                INNER JOIN `rooms` r ON b.room_id = r.id
                LEFT JOIN `doctors` doc ON s.attending_intensivist_id = doc.id
                LEFT JOIN `employees` ed ON doc.employee_id = ed.id
                WHERE 1=1";

        $params = [];

        if ($status) {
            $sql .= " AND s.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY s.admitted_at DESC LIMIT " . (int)$limit;

        return $this->db->fetchAll($sql, $params);
    }
}
