<?php

namespace App\Models;

use App\Core\Model;

class PayrollItem extends Model
{
    protected string $table = 'payroll_items';
    protected bool $timestamps = false;
    protected array $fillable = [
        'payroll_id',
        'type',
        'title',
        'amount',
    ];

    /**
     * Get items for a payroll record
     */
    public function byPayrollId(int $payrollId): array
    {
        $sql = "SELECT * FROM `payroll_items` WHERE `payroll_id` = :payroll_id ORDER BY `type` ASC, `id` ASC";
        return $this->db->fetchAll($sql, ['payroll_id' => $payrollId]);
    }

    /**
     * Delete all items for a payroll record
     */
    public function deleteByPayrollId(int $payrollId): bool
    {
        return $this->db->execute("DELETE FROM `payroll_items` WHERE `payroll_id` = :payroll_id", ['payroll_id' => $payrollId]);
    }
}
