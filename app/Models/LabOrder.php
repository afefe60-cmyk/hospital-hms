<?php

namespace App\Models;

use App\Core\Model;

class LabOrder extends Model
{
    protected string $table = 'lab_orders';
    protected bool $timestamps = false;

    protected array $fillable = [
        'order_number',
        'patient_id',
        'doctor_id',
        'encounter_id',
        'admission_id',
        'priority',
        'clinical_indication',
        'status',
        'created_at',
    ];

    public function generateOrderNumber(): string
    {
        $today = date('Ymd');
        $prefix = "LAB-{$today}-";

        $sql = "SELECT order_number FROM `lab_orders` WHERE `order_number` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
        $last = $this->db->fetchColumn($sql, ['prefix' => "{$prefix}%"]);

        if ($last) {
            $numPart = (int)substr($last, strlen($prefix));
            $next = $numPart + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    public function detailed(int $orderId): ?array
    {
        $sql = "SELECT lo.*,
                       p.mrn, p.gender, p.date_of_birth, p.blood_group, p.phone_primary,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS doctor_name,
                       s.name_ar AS doctor_specialty
                FROM `lab_orders` lo
                INNER JOIN `patients` p ON lo.patient_id = p.id
                INNER JOIN `doctors` doc ON lo.doctor_id = doc.id
                INNER JOIN `employees` e ON doc.employee_id = e.id
                INNER JOIN `specialties` s ON doc.specialty_id = s.id
                WHERE lo.id = :id";
        return $this->db->fetchOne($sql, ['id' => $orderId]);
    }

    public function items(int $orderId): array
    {
        $sql = "SELECT loi.*, lt.code AS test_code, lt.name_ar AS test_name_ar, lt.name_en AS test_name_en,
                       lt.category, lt.sample_type, lt.normal_range, lt.unit
                FROM `lab_order_items` loi
                INNER JOIN `lab_tests` lt ON loi.lab_test_id = lt.id
                WHERE loi.lab_order_id = :id";
        return $this->db->fetchAll($sql, ['id' => $orderId]);
    }

    public function sample(int $orderId): ?array
    {
        $sql = "SELECT ls.*, u.full_name AS collector_name
                FROM `lab_samples` ls
                INNER JOIN `users` u ON ls.collected_by = u.id
                WHERE ls.lab_order_id = :id
                ORDER BY ls.id DESC LIMIT 1";
        return $this->db->fetchOne($sql, ['id' => $orderId]);
    }

    public function result(int $orderId): ?array
    {
        $sql = "SELECT lr.*, u.full_name AS verifier_name
                FROM `lab_results` lr
                LEFT JOIN `users` u ON lr.verified_by = u.id
                WHERE lr.lab_order_id = :id";
        return $this->db->fetchOne($sql, ['id' => $orderId]);
    }
}
