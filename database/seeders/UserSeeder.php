<?php

class UserSeeder
{
    public function run(): void
    {
        $db = \App\Core\Database::getInstance();

        $existing = $db->fetchOne("SELECT id FROM `users` WHERE `username` = 'admin'");
        if (!$existing) {
            $passwordHash = password_hash('Password@123', PASSWORD_BCRYPT, ['cost' => 12]);

            $db->query("INSERT INTO `users` (`username`, `email`, `password_hash`, `full_name`, `phone`, `user_type`, `is_active`)
                        VALUES (:username, :email, :password_hash, :full_name, :phone, 'staff', 1)", [
                'username' => 'admin',
                'email' => 'admin@hospital-hms.sd',
                'password_hash' => $passwordHash,
                'full_name' => 'مدير النظام (Super Administrator)',
                'phone' => '+249912345678',
            ]);

            $adminId = $db->lastInsertId();

            // Assign super_admin role
            $role = $db->fetchOne("SELECT id FROM `roles` WHERE `name` = 'super_admin'");
            if ($role && $adminId) {
                $db->query("INSERT IGNORE INTO `user_roles` (`user_id`, `role_id`) VALUES (:user_id, :role_id)", [
                    'user_id' => $adminId,
                    'role_id' => $role['id'],
                ]);
            }
        }
    }
}
