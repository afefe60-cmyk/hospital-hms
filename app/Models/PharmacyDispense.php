<?php

namespace App\Models;

use App\Core\Model;

class PharmacyDispense extends Model
{
    protected string $table = 'pharmacy_dispenses';
    protected bool $timestamps = false;

    protected array $fillable = [
        'dispense_number',
        'prescription_id',
        'patient_id',
        'warehouse_id',
        'pharmacist_id',
        'total_amount',
        'payment_status',
        'notes',
        'created_at',
    ];

    public function generateDispenseNumber(): string
    {
        $today = date('Ymd');
        $prefix = "DSP-{$today}-";

        $sql = "SELECT dispense_number FROM `pharmacy_dispenses` WHERE `dispense_number` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
        $last = $this->db->fetchColumn($sql, ['prefix' => "{$prefix}%"]);

        if ($last) {
            $numPart = (int)substr($last, strlen($prefix));
            $next = $numPart + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    public function detailed(int $dispenseId): ?array
    {
        $sql = "SELECT pd.*,
                       p.mrn, p.gender,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                       w.name_ar AS warehouse_name,
                       u.full_name AS pharmacist_name,
                       rx.prescription_code
                FROM `pharmacy_dispenses` pd
                INNER JOIN `patients` p ON pd.patient_id = p.id
                INNER JOIN `warehouses` w ON pd.warehouse_id = w.id
                INNER JOIN `users` u ON pd.pharmacist_id = u.id
                LEFT JOIN `prescriptions` rx ON pd.prescription_id = rx.id
                WHERE pd.id = :id";
        return $this->db->fetchOne($sql, ['id' => $dispenseId]);
    }

    public function items(int $dispenseId): array
    {
        $sql = "SELECT pdi.*,
                       m.commercial_name, m.scientific_name, m.strength, m.dosage_form,
                       b.batch_number, b.expiry_date
                FROM `pharmacy_dispense_items` pdi
                INNER JOIN `medicines` m ON pdi.medicine_id = m.id
                INNER JOIN `medicine_batches` b ON pdi.batch_id = b.id
                WHERE pdi.pharmacy_dispense_id = :id";
        return $this->db->fetchAll($sql, ['id' => $dispenseId]);
    }
}
