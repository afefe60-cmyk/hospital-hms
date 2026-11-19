<?php

namespace App\Models;

use App\Core\Model;

class SystemSetting extends Model
{
    protected string $table = 'system_settings';
    protected string $primaryKey = 'setting_key';
    protected bool $timestamps = false;
    protected array $fillable = [
        'setting_key',
        'setting_value',
        'setting_group',
        'description',
    ];

    /**
     * Get setting value by key, or return default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $instance = new self();
        $row = $instance->db->fetchOne(
            "SELECT `setting_value` FROM `system_settings` WHERE `setting_key` = :key LIMIT 1",
            ['key' => $key]
        );

        return ($row && isset($row['setting_value'])) ? $row['setting_value'] : $default;
    }

    /**
     * Set / update setting value
     */
    public static function set(string $key, mixed $value, string $group = 'general', ?string $description = null): bool
    {
        $instance = new self();
        $strValue = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value;

        $existing = $instance->db->fetchOne(
            "SELECT `setting_key` FROM `system_settings` WHERE `setting_key` = :key LIMIT 1",
            ['key' => $key]
        );

        if ($existing) {
            $sql = "UPDATE `system_settings` SET `setting_value` = :val, `setting_group` = :grp";
            $params = ['val' => $strValue, 'grp' => $group, 'key' => $key];
            if ($description !== null) {
                $sql .= ", `description` = :desc";
                $params['desc'] = $description;
            }
            $sql .= " WHERE `setting_key` = :key";
            $instance->db->execute($sql, $params);
            return true;
        }

        $instance->create([
            'setting_key'   => $key,
            'setting_value' => $strValue,
            'setting_group' => $group,
            'description'   => $description,
        ]);

        return true;
    }

    /**
     * Get all settings in a specific group
     */
    public function getByGroup(string $group): array
    {
        $sql = "SELECT * FROM `system_settings` WHERE `setting_group` = :grp ORDER BY `setting_key` ASC";
        $rows = $this->db->fetchAll($sql, ['grp' => $group]);
        $result = [];
        foreach ($rows as $r) {
            $result[$r['setting_key']] = $r['setting_value'];
        }
        return $result;
    }

    /**
     * Get all settings grouped by category
     */
    public function allGrouped(): array
    {
        $rows = $this->all('setting_group', 'ASC');
        $grouped = [];
        foreach ($rows as $r) {
            $grp = $r['setting_group'];
            if (!isset($grouped[$grp])) {
                $grouped[$grp] = [];
            }
            $grouped[$grp][$r['setting_key']] = $r;
        }
        return $grouped;
    }

    /**
     * Get all settings as key-value array
     */
    public function allKeyValue(): array
    {
        $rows = $this->all('setting_key', 'ASC');
        $kv = [];
        foreach ($rows as $r) {
            $kv[$r['setting_key']] = $r['setting_value'];
        }
        return $kv;
    }
}
