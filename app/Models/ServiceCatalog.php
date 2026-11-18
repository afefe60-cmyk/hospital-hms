<?php

namespace App\Models;

use App\Core\Model;

class ServiceCatalog extends Model
{
    protected string $table = 'service_catalog';
    protected bool $timestamps = false;

    protected array $fillable = [
        'code',
        'name_ar',
        'name_en',
        'department_id',
        'revenue_account_id',
        'price_standard',
        'is_active',
        'created_at',
    ];

    public function activeServices(?int $departmentId = null): array
    {
        $sql = "SELECT s.*, d.name_ar AS department_name
                FROM service_catalog s
                INNER JOIN departments d ON s.department_id = d.id
                WHERE s.is_active = 1";
        $params = [];
        if ($departmentId) {
            $sql .= " AND s.department_id = :dep_id";
            $params['dep_id'] = $departmentId;
        }
        $sql .= " ORDER BY s.name_ar ASC";
        return $this->db->fetchAll($sql, $params);
    }
}
