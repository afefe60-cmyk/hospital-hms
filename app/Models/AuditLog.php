<?php

namespace App\Models;

use App\Core\Model;

class AuditLog extends Model
{
    protected string $table = 'audit_logs';
    protected bool $timestamps = false;
    protected array $fillable = [
        'user_id',
        'action',
        'table_name',
        'record_id',
        'old_values',
        'new_values',
        'ip_address',
        'created_at',
    ];

    public static function log(
        string $action,
        string $tableName,
        int|string $recordId,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null,
        ?string $ip = null
    ): void {
        $db = \App\Core\Database::getInstance();

        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $userId = $userId ?? \App\Core\Session::get('user_id');

        $sql = "INSERT INTO `audit_logs` (`user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `created_at`)
                VALUES (:user_id, :action, :table_name, :record_id, :old_values, :new_values, :ip_address, NOW())";

        $db->query($sql, [
            'user_id' => $userId,
            'action' => strtoupper($action),
            'table_name' => $tableName,
            'record_id' => (int)$recordId,
            'old_values' => $oldValues !== null ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            'new_values' => $newValues !== null ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $ip,
        ]);
    }

    public function search(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $conditions = [];
        $params = [];

        if (!empty($filters['action'])) {
            $conditions[] = "a.action = :action";
            $params['action'] = $filters['action'];
        }

        if (!empty($filters['table_name'])) {
            $conditions[] = "a.table_name = :table_name";
            $params['table_name'] = $filters['table_name'];
        }

        if (!empty($filters['user_id'])) {
            $conditions[] = "a.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $countSql = "SELECT COUNT(*) FROM `audit_logs` a {$whereClause}";
        $totalRecords = (int)$this->db->fetchColumn($countSql, $params);
        $totalPages = (int)ceil($totalRecords / $perPage);

        $sql = "SELECT a.*, u.username, u.full_name FROM `audit_logs` a
                LEFT JOIN `users` u ON a.user_id = u.id
                {$whereClause}
                ORDER BY a.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(":{$k}", $v);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        return [
            'data' => $items,
            'total' => $totalRecords,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $totalPages,
        ];
    }
}
