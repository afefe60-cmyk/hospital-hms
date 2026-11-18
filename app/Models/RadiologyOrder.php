<?php

namespace App\Models;

use App\Core\Model;

class RadiologyOrder extends Model
{
    protected string $table = 'radiology_orders';
    protected bool $timestamps = false;

    protected array $fillable = [
        'order_number',
        'patient_id',
        'doctor_id',
        'encounter_id',
        'admission_id',
        'service_id',
        'priority',
        'clinical_indication',
        'status',
        'created_at',
    ];

    public function generateOrderNumber(): string
    {
        $today = date('Ymd');
        $prefix = "RAD-{$today}-";

        $sql = "SELECT order_number FROM `radiology_orders` WHERE `order_number` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
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
        $sql = "SELECT ro.*,
                       p.mrn, p.gender, p.date_of_birth, p.blood_group, p.phone_primary,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS doctor_name,
                       s.name_ar AS doctor_specialty,
                       rs.code AS service_code, rs.name_ar AS service_name_ar, rs.name_en AS service_name_en,
                       rs.modality, rs.price, rs.preparation_instructions
                FROM `radiology_orders` ro
                INNER JOIN `patients` p ON ro.patient_id = p.id
                INNER JOIN `doctors` doc ON ro.doctor_id = doc.id
                INNER JOIN `employees` e ON doc.employee_id = e.id
                INNER JOIN `specialties` s ON doc.specialty_id = s.id
                INNER JOIN `radiology_services` rs ON ro.service_id = rs.id
                WHERE ro.id = :id";
        return $this->db->fetchOne($sql, ['id' => $orderId]);
    }

    public function report(int $orderId): ?array
    {
        $sql = "SELECT rr.*,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS radiologist_name,
                       s.name_ar AS radiologist_specialty
                FROM `radiology_reports` rr
                INNER JOIN `doctors` d ON rr.radiologist_id = d.id
                INNER JOIN `employees` e ON d.employee_id = e.id
                INNER JOIN `specialties` s ON d.specialty_id = s.id
                WHERE rr.radiology_order_id = :id";
        return $this->db->fetchOne($sql, ['id' => $orderId]);
    }

    public function attachments(int $orderId): array
    {
        $sql = "SELECT * FROM `radiology_attachments` WHERE `radiology_order_id` = :id ORDER BY `id` ASC";
        return $this->db->fetchAll($sql, ['id' => $orderId]);
    }
}
