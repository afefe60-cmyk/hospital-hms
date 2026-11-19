<?php

namespace App\Models;

use App\Core\Model;

class Patient extends Model
{
    protected string $table = 'patients';
    protected array $fillable = [
        'mrn',
        'national_id',
        'first_name',
        'second_name',
        'third_name',
        'last_name',
        'gender',
        'date_of_birth',
        'blood_group',
        'marital_status',
        'phone_primary',
        'phone_secondary',
        'address_state',
        'address_city',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'is_deceased',
        'deceased_date',
        'created_by',
    ];

    /**
     * Generate the next unique MRN (e.g. MRN-2026-000001)
     */
    public function generateNextMrn(): string
    {
        $year = date('Y');
        $prefix = "MRN-{$year}-";

        $sql = "SELECT mrn FROM `patients` WHERE `mrn` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
        $lastMrn = $this->db->fetchColumn($sql, ['prefix' => "{$prefix}%"]);

        if ($lastMrn) {
            $numPart = (int)substr($lastMrn, strlen($prefix));
            $nextNum = $numPart + 1;
        } else {
            $nextNum = 1;
        }

        return $prefix . str_pad((string)$nextNum, 6, '0', STR_PAD_LEFT);
    }

    public function search(string $query, int $page = 1, int $perPage = 15): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $conditions = [];
        $params = [];

        if (!empty($query)) {
            $conditions[] = "(`mrn` LIKE :q OR `national_id` LIKE :q OR `phone_primary` LIKE :q OR `first_name` LIKE :q OR `last_name` LIKE :q OR CONCAT(`first_name`, ' ', `second_name`, ' ', `last_name`) LIKE :q)";
            $params['q'] = "%{$query}%";
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $totalRecords = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM `patients` {$whereClause}", $params);
        $totalPages = (int)ceil($totalRecords / $perPage);

        $sql = "SELECT p.*,
                       TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age,
                       (SELECT COUNT(*) FROM `patient_allergies` WHERE `patient_id` = p.id) AS allergies_count
                FROM `patients` p
                {$whereClause}
                ORDER BY p.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(":{$k}", $v);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $patients = $stmt->fetchAll();

        return [
            'data' => $patients,
            'total' => $totalRecords,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $totalPages,
        ];
    }

    public function allergies(int $patientId): array
    {
        $sql = "SELECT a.*, u.full_name AS recorded_by_name
                FROM `patient_allergies` a
                LEFT JOIN `users` u ON a.recorded_by = u.id
                WHERE a.patient_id = :id
                ORDER BY a.severity = 'life_threatening' DESC, a.severity = 'severe' DESC, a.id DESC";
        return $this->db->fetchAll($sql, ['id' => $patientId]);
    }

    public function conditions(int $patientId): array
    {
        $sql = "SELECT * FROM `patient_conditions` WHERE `patient_id` = :id ORDER BY `is_active` DESC, `id` DESC";
        return $this->db->fetchAll($sql, ['id' => $patientId]);
    }

    public function appointments(int $patientId): array
    {
        $sql = "SELECT a.*,
                       doc.degree_title,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS doctor_name,
                       s.name_ar AS specialty_name,
                       c.name_ar AS clinic_name, c.room_number
                FROM `appointments` a
                INNER JOIN `doctors` doc ON a.doctor_id = doc.id
                INNER JOIN `employees` e ON doc.employee_id = e.id
                INNER JOIN `specialties` s ON doc.specialty_id = s.id
                INNER JOIN `clinics` c ON a.clinic_id = c.id
                WHERE a.patient_id = :id
                ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        return $this->db->fetchAll($sql, ['id' => $patientId]);
    }

    public function visits(int $patientId): array
    {
        $sql = "SELECT v.*,
                       d.name_ar AS department_name,
                       CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS doctor_name
                FROM `visits` v
                INNER JOIN `departments` d ON v.department_id = d.id
                LEFT JOIN `doctors` doc ON v.attending_doctor_id = doc.id
                LEFT JOIN `employees` e ON doc.employee_id = e.id
                WHERE v.patient_id = :id
                ORDER BY v.id DESC";
        return $this->db->fetchAll($sql, ['id' => $patientId]);
    }

    public function admissions(int $patientId): array
    {
        $sql = "SELECT a.*, w.name_ar as ward_name, b.bed_number, r.room_number,
                       CONCAT(e.first_name, ' ', e.last_name) as doctor_name
                FROM `admissions` a
                LEFT JOIN `bed_assignments` ba ON a.id = ba.admission_id AND ba.is_active = 1
                LEFT JOIN `beds` b ON ba.bed_id = b.id
                LEFT JOIN `rooms` r ON b.room_id = r.id
                LEFT JOIN `wards` w ON r.ward_id = w.id
                LEFT JOIN `doctors` doc ON a.admitting_doctor_id = doc.id
                LEFT JOIN `employees` e ON doc.employee_id = e.id
                WHERE a.patient_id = :id
                ORDER BY a.id DESC";
        return $this->db->fetchAll($sql, ['id' => $patientId]);
    }

    public function labOrders(int $patientId): array
    {
        $sql = "SELECT lo.*, 
                       CONCAT(e.first_name, ' ', e.last_name) as doctor_name,
                       (SELECT GROUP_CONCAT(lt.name_ar SEPARATOR '، ') 
                        FROM `lab_order_items` loi 
                        JOIN `lab_tests` lt ON loi.lab_test_id = lt.id 
                        WHERE loi.lab_order_id = lo.id) as test_names
                FROM `lab_orders` lo
                LEFT JOIN `doctors` doc ON lo.doctor_id = doc.id
                LEFT JOIN `employees` e ON doc.employee_id = e.id
                WHERE lo.patient_id = :id
                ORDER BY lo.id DESC";
        return $this->db->fetchAll($sql, ['id' => $patientId]);
    }

    public function radiologyOrders(int $patientId): array
    {
        $sql = "SELECT ro.*, rs.name_ar as service_name, rs.modality,
                       CONCAT(e.first_name, ' ', e.last_name) as doctor_name
                FROM `radiology_orders` ro
                LEFT JOIN `radiology_services` rs ON ro.service_id = rs.id
                LEFT JOIN `doctors` doc ON ro.doctor_id = doc.id
                LEFT JOIN `employees` e ON doc.employee_id = e.id
                WHERE ro.patient_id = :id
                ORDER BY ro.id DESC";
        return $this->db->fetchAll($sql, ['id' => $patientId]);
    }

    public function invoices(int $patientId): array
    {
        $sql = "SELECT inv.*
                FROM `invoices` inv
                WHERE inv.patient_id = :id
                ORDER BY inv.id DESC";
        return $this->db->fetchAll($sql, ['id' => $patientId]);
    }

    public function surgeries(int $patientId): array
    {
        $sql = "SELECT s.*, op.name_ar as operating_room,
                       CONCAT(e.first_name, ' ', e.last_name) as surgeon_name
                FROM `surgeries` s
                LEFT JOIN `operating_rooms` op ON s.operating_room_id = op.id
                LEFT JOIN `doctors` doc ON s.lead_surgeon_id = doc.id
                LEFT JOIN `employees` e ON doc.employee_id = e.id
                WHERE s.patient_id = :id
                ORDER BY s.id DESC";
        return $this->db->fetchAll($sql, ['id' => $patientId]);
    }
}
