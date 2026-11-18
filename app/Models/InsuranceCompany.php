<?php

namespace App\Models;

use App\Core\Model;

class InsuranceCompany extends Model
{
    protected string $table = 'insurance_companies';
    protected bool $timestamps = false;

    protected array $fillable = [
        'code',
        'name_ar',
        'name_en',
        'account_id',
        'phone',
        'email',
        'address',
        'default_coverage_rate',
        'is_active',
        'created_at',
    ];

    public function activeCompanies(): array
    {
        return $this->db->fetchAll("SELECT * FROM insurance_companies WHERE is_active = 1 ORDER BY name_ar ASC");
    }
}
