<?php

namespace App\Models;

use App\Core\Model;

class Encounter extends Model
{
    protected string $table = 'encounters';
    protected bool $timestamps = false;
    protected array $fillable = [
        'visit_id',
        'doctor_id',
        'encounter_type',
        'chief_complaint',
        'history_present_illness',
        'physical_examination',
        'clinical_plan',
        'start_time',
        'end_time',
        'created_at',
    ];

    public function detailed(int $encounterId): ?array
    {
        $sql = "SELECT enc.*,
                       v.visit_number, v.visit_type, v.arrival_time,
                       p.id AS patient_id, p.mrn, p.gender, p.date_of_birth, p.blood_group,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                       p.phone_primary,
                       doc.degree_title,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS doctor_name,
                       s.name_ar AS specialty_name
                FROM `encounters` enc
                INNER JOIN `visits` v ON enc.visit_id = v.id
                INNER JOIN `patients` p ON v.patient_id = p.id
                INNER JOIN `doctors` doc ON enc.doctor_id = doc.id
                INNER JOIN `employees` e ON doc.employee_id = e.id
                INNER JOIN `specialties` s ON doc.specialty_id = s.id
                WHERE enc.id = :id LIMIT 1";
        return $this->db->fetchOne($sql, ['id' => $encounterId]);
    }

    public function vitals(int $encounterId): ?array
    {
        $sql = "SELECT * FROM `vital_signs` WHERE `encounter_id` = :id ORDER BY `id` DESC LIMIT 1";
        return $this->db->fetchOne($sql, ['id' => $encounterId]);
    }

    public function diagnoses(int $encounterId): array
    {
        $sql = "SELECT d.*, c.icd10_code, c.description_ar, c.description_en, c.is_infectious
                FROM `diagnoses` d
                INNER JOIN `diagnosis_catalog` c ON d.diagnosis_catalog_id = c.id
                WHERE d.encounter_id = :id
                ORDER BY d.type = 'primary' DESC, d.id ASC";
        return $this->db->fetchAll($sql, ['id' => $encounterId]);
    }

    public function prescriptions(int $encounterId): array
    {
        $sql = "SELECT p.*,
                       (SELECT COUNT(*) FROM `prescription_items` WHERE `prescription_id` = p.id) AS items_count
                FROM `prescriptions` p
                WHERE p.encounter_id = :id
                ORDER BY p.id DESC";
        return $this->db->fetchAll($sql, ['id' => $encounterId]);
    }

    public function orders(int $encounterId): array
    {
        $sql = "SELECT * FROM `medical_orders` WHERE `encounter_id` = :id ORDER BY `id` DESC";
        return $this->db->fetchAll($sql, ['id' => $encounterId]);
    }
}
