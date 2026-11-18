<?php

namespace App\Models;

use App\Core\Model;

class Permission extends Model
{
    protected string $table = 'permissions';
    protected array $fillable = [
        'code',
        'module',
        'action',
        'name_ar',
        'name_en',
    ];

    public function allGroupedByModule(): array
    {
        $all = $this->all('module', 'ASC');
        $grouped = [];

        foreach ($all as $perm) {
            $module = $perm['module'];
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][] = $perm;
        }

        return $grouped;
    }
}
