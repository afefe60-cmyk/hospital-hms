<?php

namespace App\Models;

use App\Core\Model;

class Appointment extends Model
{
    protected string $table = 'appointments';
    protected array $fillable = [
        'patient_id',
        'doctor_id',
        'clinic_id',
        'appointment_date',
        'appointment_time',
        'token_number',
        'status',
        'cancellation_reason',
        'created_by',
    ];

    public function generateToken(int $clinicId, string $date): int
    {
        $sql = "SELECT MAX(token_number) FROM `appointments` WHERE `clinic_id` = :clinic AND `appointment_date` = :date";
        $max = (int)$this->db->fetchColumn($sql, ['clinic' => $clinicId, 'date' => $date]);
        return $max + 1;
    }

    public function allDetailed(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $conditions = [];
        $params = [];

        if (!empty($filters['date'])) {
            $conditions[] = "a.appointment_date = :date";
            $params['date'] = $filters['date'];
        }

        if (!empty($filters['doctor_id'])) {
            $conditions[] = "a.doctor_id = :doc_id";
            $params['doc_id'] = $filters['doctor_id'];
        }

        if (!empty($filters['clinic_id'])) {
            $conditions[] = "a.clinic_id = :clinic_id";
            $params['clinic_id'] = $filters['clinic_id'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "a.status = :status";
            $params['status'] = $filters['status'];
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $totalRecords = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM `appointments` a {$whereClause}", $params);
        $totalPages = (int)ceil($totalRecords / $perPage);

        $sql = "SELECT a.*,
                       p.mrn, p.phone_primary,
                       CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS doctor_name,
                       s.name_ar AS specialty_name,
                       c.name_ar AS clinic_name, c.room_number
                FROM `appointments` a
                INNER JOIN `patients` p ON a.patient_id = p.id
                INNER JOIN `doctors` doc ON a.doctor_id = doc.id
                INNER JOIN `employees` e ON doc.employee_id = e.id
                INNER JOIN `specialties` s ON doc.specialty_id = s.id
                INNER JOIN `clinics` c ON a.clinic_id = c.id
                {$whereClause}
                ORDER BY a.appointment_date DESC, a.token_number ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(":{$k}", $v);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $appointments = $stmt->fetchAll();

        return [
            'data' => $appointments,
            'total' => $totalRecords,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $totalPages,
        ];
    }
}
