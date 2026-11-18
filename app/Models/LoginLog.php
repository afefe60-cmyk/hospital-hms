<?php

namespace App\Models;

use App\Core\Model;

class LoginLog extends Model
{
    protected string $table = 'login_logs';
    protected bool $timestamps = false;
    protected array $fillable = [
        'user_id',
        'username_attempted',
        'ip_address',
        'user_agent',
        'status',
        'attempted_at',
    ];

    public static function record(?int $userId, string $username, string $ip, ?string $userAgent, string $status): void
    {
        $db = \App\Core\Database::getInstance();
        $sql = "INSERT INTO `login_logs` (`user_id`, `username_attempted`, `ip_address`, `user_agent`, `status`, `attempted_at`)
                VALUES (:user_id, :username, :ip, :ua, :status, NOW())";
        $db->query($sql, [
            'user_id' => $userId,
            'username' => $username,
            'ip' => $ip,
            'ua' => substr((string)$userAgent, 0, 255),
            'status' => $status,
        ]);
    }
}
