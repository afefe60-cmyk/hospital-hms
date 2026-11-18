<?php

namespace App\Core;

abstract class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected bool $timestamps = true;
    protected array $fillable = [];
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        if (empty($this->table)) {
            // Default table name to snake_case plural of class name
            $reflection = new \ReflectionClass($this);
            $shortName = $reflection->getShortName();
            $this->table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $shortName)) . 's';
        }
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function find(mixed $id): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    public function all(string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $sql = "SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}` {$direction}";
        return $this->db->fetchAll($sql);
    }

    public function where(string $column, mixed $operatorOrValue, mixed $value = null): array
    {
        if ($value === null) {
            $operator = '=';
            $val = $operatorOrValue;
        } else {
            $operator = $operatorOrValue;
            $val = $value;
        }

        $sql = "SELECT * FROM `{$this->table}` WHERE `{$column}` {$operator} :val";
        return $this->db->fetchAll($sql, ['val' => $val]);
    }

    public function firstWhere(string $column, mixed $operatorOrValue, mixed $value = null): ?array
    {
        if ($value === null) {
            $operator = '=';
            $val = $operatorOrValue;
        } else {
            $operator = $operatorOrValue;
            $val = $value;
        }

        $sql = "SELECT * FROM `{$this->table}` WHERE `{$column}` {$operator} :val LIMIT 1";
        return $this->db->fetchOne($sql, ['val' => $val]);
    }

    public function create(array $attributes): int|string
    {
        $data = $this->filterFillable($attributes);

        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
        }

        $columns = array_keys($data);
        $fields = implode('`, `', $columns);
        $placeholders = ':' . implode(', :', $columns);

        $sql = "INSERT INTO `{$this->table}` (`{$fields}`) VALUES ({$placeholders})";
        $this->db->query($sql, $data);

        return $this->db->lastInsertId();
    }

    public function update(mixed $id, array $attributes): bool
    {
        $data = $this->filterFillable($attributes);

        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "`{$column}` = :{$column}";
        }
        $setString = implode(', ', $sets);

        $sql = "UPDATE `{$this->table}` SET {$setString} WHERE `{$this->primaryKey}` = :primary_id";
        $data['primary_id'] = $id;

        $this->db->query($sql, $data);
        return true;
    }

    public function delete(mixed $id): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        $this->db->query($sql, ['id' => $id]);
        return true;
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}`";
        return (int)$this->db->fetchColumn($sql);
    }

    public function paginate(int $page = 1, int $perPage = 20, string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $totalRecords = $this->count();
        $totalPages = (int)ceil($totalRecords / $perPage);

        $sql = "SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}` {$direction} LIMIT :limit OFFSET :offset";
        $stmt = $this->db->getConnection()->prepare($sql);
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
            'from' => $totalRecords > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $totalRecords),
        ];
    }

    protected function filterFillable(array $attributes): array
    {
        if (empty($this->fillable)) {
            return $attributes;
        }

        return array_intersect_key($attributes, array_flip($this->fillable));
    }
}
