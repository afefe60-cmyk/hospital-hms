<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';
    protected array $fillable = [
        'username',
        'email',
        'password_hash',
        'full_name',
        'phone',
        'user_type',
        'is_active',
        'failed_logins',
        'locked_until',
        'last_login_at',
        'last_login_ip',
    ];

    public function findByUsername(string $username): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `username` = :username OR `email` = :email LIMIT 1";
        return $this->db->fetchOne($sql, ['username' => $username, 'email' => $username]);
    }

    public function roles(int $userId): array
    {
        $sql = "SELECT r.* FROM `roles` r
                INNER JOIN `user_roles` ur ON r.id = ur.role_id
                WHERE ur.user_id = :user_id";
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }

    public function hasRole(int $userId, string $roleName): bool
    {
        $sql = "SELECT COUNT(*) FROM `roles` r
                INNER JOIN `user_roles` ur ON r.id = ur.role_id
                WHERE ur.user_id = :user_id AND r.name = :name";
        return (int)$this->db->fetchColumn($sql, ['user_id' => $userId, 'name' => $roleName]) > 0;
    }

    public function permissions(int $userId): array
    {
        // If super_admin, return all permissions
        if ($this->hasRole($userId, 'super_admin')) {
            $sql = "SELECT code FROM `permissions`";
            $all = $this->db->fetchAll($sql);
            return array_column($all, 'code');
        }

        $sql = "SELECT DISTINCT p.code FROM `permissions` p
                INNER JOIN `role_permissions` rp ON p.id = rp.permission_id
                INNER JOIN `user_roles` ur ON rp.role_id = ur.role_id
                WHERE ur.user_id = :user_id";
        $results = $this->db->fetchAll($sql, ['user_id' => $userId]);
        return array_column($results, 'code');
    }

    public function hasPermission(int $userId, string $permissionCode): bool
    {
        if ($this->hasRole($userId, 'super_admin')) {
            return true;
        }

        $perms = $this->permissions($userId);
        return in_array($permissionCode, $perms, true);
    }

    public function isLocked(array $user): bool
    {
        if (!empty($user['locked_until'])) {
            $lockTime = strtotime($user['locked_until']);
            if ($lockTime > time()) {
                return true;
            }
        }
        return false;
    }

    public function recordLoginSuccess(int $userId, string $ip): void
    {
        $sql = "UPDATE `{$this->table}` SET
                `failed_logins` = 0,
                `locked_until` = NULL,
                `last_login_at` = NOW(),
                `last_login_ip` = :ip
                WHERE `id` = :id";
        $this->db->query($sql, ['ip' => $ip, 'id' => $userId]);
    }

    public function recordLoginFailure(int $userId, int $currentFailures): void
    {
        $newFailures = $currentFailures + 1;
        $lockUntil = null;

        // Lock for 15 minutes if 5 failed attempts
        if ($newFailures >= 5) {
            $lockUntil = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        }

        $sql = "UPDATE `{$this->table}` SET
                `failed_logins` = :failures,
                `locked_until` = :locked
                WHERE `id` = :id";
        $this->db->query($sql, [
            'failures' => $newFailures,
            'locked' => $lockUntil,
            'id' => $userId,
        ]);
    }

    public function syncRoles(int $userId, array $roleIds, ?int $assignedBy = null): void
    {
        $this->db->query("DELETE FROM `user_roles` WHERE `user_id` = :user_id", ['user_id' => $userId]);

        foreach ($roleIds as $roleId) {
            $this->db->query("INSERT INTO `user_roles` (`user_id`, `role_id`, `assigned_by`)
                             VALUES (:user_id, :role_id, :assigned_by)", [
                'user_id' => $userId,
                'role_id' => (int)$roleId,
                'assigned_by' => $assignedBy,
            ]);
        }
    }
}
