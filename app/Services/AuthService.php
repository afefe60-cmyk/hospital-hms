<?php

namespace App\Services;

use App\Core\Session;
use App\Models\AuditLog;
use App\Models\LoginLog;
use App\Models\User;

class AuthService
{
    private User $userModel;
    private static ?array $currentUser = null;
    private static ?array $userPermissions = null;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Attempt login
     */
    public function attempt(string $username, string $password, string $ip, ?string $userAgent): array
    {
        $username = trim($username);
        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            LoginLog::record(null, $username, $ip, $userAgent, 'failed');
            return [
                'success' => false,
                'message' => 'بيانات الدخول غير صحيحة، يرجى التأكد من اسم المستخدم وكلمة المرور.',
            ];
        }

        // Check if account is deactivated
        if (!$user['is_active']) {
            LoginLog::record((int)$user['id'], $username, $ip, $userAgent, 'failed');
            return [
                'success' => false,
                'message' => 'هذا الحساب معطل مؤقتاً. يرجى التواصل مع إدارة النظام.',
            ];
        }

        // Check if locked
        if ($this->userModel->isLocked($user)) {
            $lockTime = strtotime($user['locked_until']);
            $remainingMins = max(1, (int)ceil(($lockTime - time()) / 60));
            LoginLog::record((int)$user['id'], $username, $ip, $userAgent, 'locked');
            return [
                'success' => false,
                'message' => "تم قفل الحساب مؤقتاً لتكرار المحاولات الخاطئة. يرجى المحاولة بعد {$remainingMins} دقيقة.",
            ];
        }

        // Verify Password
        if (!password_verify($password, $user['password_hash'])) {
            $this->userModel->recordLoginFailure((int)$user['id'], (int)$user['failed_logins']);
            LoginLog::record((int)$user['id'], $username, $ip, $userAgent, 'failed');

            $remainingAttempts = max(0, 5 - ((int)$user['failed_logins'] + 1));
            $msg = 'بيانات الدخول غير صحيحة.';
            if ($remainingAttempts > 0 && $remainingAttempts <= 3) {
                $msg .= " تبقى لديك {$remainingAttempts} محاولات قبل قفل الحساب.";
            }

            return [
                'success' => false,
                'message' => $msg,
            ];
        }

        // Login success
        $this->userModel->recordLoginSuccess((int)$user['id'], $ip);
        LoginLog::record((int)$user['id'], $username, $ip, $userAgent, 'success');

        // Regenerate session to prevent fixation
        Session::regenerate(true);
        Session::set('user_id', (int)$user['id']);
        Session::set('username', $user['username']);
        Session::set('full_name', $user['full_name']);
        Session::set('user_type', $user['user_type']);

        // Audit log login
        AuditLog::log('LOGIN', 'users', $user['id'], null, ['ip' => $ip, 'ua' => $userAgent], (int)$user['id'], $ip);

        self::$currentUser = $user;
        self::$userPermissions = null;

        return [
            'success' => true,
            'user' => $user,
        ];
    }

    /**
     * Check if currently authenticated
     */
    public function check(): bool
    {
        return Session::has('user_id');
    }

    /**
     * Get current authenticated user ID
     */
    public function id(): ?int
    {
        return Session::get('user_id');
    }

    /**
     * Get current authenticated user details
     */
    public function user(): ?array
    {
        if (!$this->check()) {
            return null;
        }

        if (self::$currentUser === null) {
            $id = $this->id();
            self::$currentUser = $this->userModel->find($id);
        }

        return self::$currentUser;
    }

    /**
     * Check if current user has permission
     */
    public function can(string $permissionCode): bool
    {
        $userId = $this->id();
        if (!$userId) {
            return false;
        }

        if (self::$userPermissions === null) {
            self::$userPermissions = $this->userModel->permissions($userId);
        }

        if ($this->hasRole('super_admin')) {
            return true;
        }

        return in_array($permissionCode, self::$userPermissions, true);
    }

    /**
     * Check if current user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        $userId = $this->id();
        if (!$userId) {
            return false;
        }

        return $this->userModel->hasRole($userId, $roleName);
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        $userId = $this->id();
        if ($userId) {
            AuditLog::log('LOGOUT', 'users', $userId, null, null, $userId);
        }

        Session::destroy();
        self::$currentUser = null;
        self::$userPermissions = null;
    }

    /**
     * Change user password
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword): array
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'المستخدم غير موجود.'];
        }

        if (!password_verify($oldPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'كلمة المرور الحالية غير صحيحة.'];
        }

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'يجب ألا تقل كلمة المرور الجديدة عن 8 أحرف.'];
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->userModel->update($userId, ['password_hash' => $newHash]);

        AuditLog::log('PASSWORD_CHANGE', 'users', $userId, null, null, $userId);

        return ['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح.'];
    }
}
