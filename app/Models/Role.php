<?php

namespace App\Models;

use App\Core\Model;

class Role extends Model
{
    protected string $table = 'roles';
    protected array $fillable = [
        'name',
        'display_name_ar',
        'display_name_en',
        'description',
        'is_system',
    ];

    public function permissions(int $roleId): array
    {
        $sql = "SELECT p.* FROM `permissions` p
                INNER JOIN `role_permissions` rp ON p.id = rp.permission_id
                WHERE rp.role_id = :role_id";
        return $this->db->fetchAll($sql, ['role_id' => $roleId]);
    }

    public function permissionIds(int $roleId): array
    {
        $sql = "SELECT permission_id FROM `role_permissions` WHERE `role_id` = :role_id";
        $results = $this->db->fetchAll($sql, ['role_id' => $roleId]);
        return array_column($results, 'permission_id');
    }

    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $this->db->query("DELETE FROM `role_permissions` WHERE `role_id` = :role_id", ['role_id' => $roleId]);

        foreach ($permissionIds as $permId) {
            $this->db->query("INSERT INTO `role_permissions` (`role_id`, `permission_id`)
                             VALUES (:role_id, :permission_id)", [
                'role_id' => $roleId,
                'permission_id' => (int)$permId,
            ]);
        }
    }

    public function userCount(int $roleId): int
    {
        $sql = "SELECT COUNT(*) FROM `user_roles` WHERE `role_id` = :role_id";
        return (int)$this->db->fetchColumn($sql, ['role_id' => $roleId]);
    }
}
