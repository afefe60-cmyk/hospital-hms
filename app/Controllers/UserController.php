<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;

class UserController extends Controller
{
    private User $userModel;
    private Role $roleModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->roleModel = new Role();
    }

    public function index(Request $request, Response $response): string
    {
        if (!can('users.view')) {
            Session::flash('error', 'ليس لديك صلاحية عرض قائمة المستخدمين.');
            $this->redirect(url('/'));
        }

        $page = (int)$request->query('page', 1);
        $search = trim((string)$request->query('search', ''));

        $db = \App\Core\Database::getInstance();
        $where = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "(username LIKE :search OR full_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $totalRecords = (int)$db->fetchColumn("SELECT COUNT(*) FROM `users` {$whereClause}", $params);
        $perPage = 15;
        $totalPages = (int)ceil($totalRecords / $perPage);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT u.*, GROUP_CONCAT(r.display_name_ar SEPARATOR '، ') AS roles_ar
                FROM `users` u
                LEFT JOIN `user_roles` ur ON u.id = ur.user_id
                LEFT JOIN `roles` r ON ur.role_id = r.id
                {$whereClause}
                GROUP BY u.id
                ORDER BY u.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $db->getConnection()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(":{$k}", $v);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll();

        return $this->view('users/index', [
            'pageTitle' => 'إدارة المستخدمين',
            'users' => $users,
            'search' => $search,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalRecords' => $totalRecords,
        ]);
    }

    public function create(Request $request, Response $response): string
    {
        if (!can('users.create')) {
            Session::flash('error', 'ليس لديك صلاحية إضافة مستخدم جديد.');
            $this->redirect(url('/users'));
        }

        $roles = $this->roleModel->all('id', 'ASC');

        return $this->view('users/create', [
            'pageTitle' => 'إضافة مستخدم جديد',
            'roles' => $roles,
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        if (!can('users.create')) {
            Session::flash('error', 'غير مصرح.');
            $this->redirect(url('/users'));
            return;
        }

        $validation = $this->validate($request->all(), [
            'username' => 'required|min:3|max:50',
            'full_name' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if (!$validation['isValid']) {
            $this->redirect(url('/users/create'));
            return;
        }

        $username = trim((string)$request->input('username'));
        $email = trim((string)$request->input('email'));

        // Check if username or email already exists
        $existing = $this->userModel->findByUsername($username);
        if ($existing) {
            Session::flash('error', 'اسم المستخدم أو البريد الإلكتروني مسجل مسبقاً.');
            Session::flash('_old_input', $request->all());
            $this->redirect(url('/users/create'));
            return;
        }

        $passwordHash = password_hash((string)$request->input('password'), PASSWORD_BCRYPT, ['cost' => 12]);

        $userId = $this->userModel->create([
            'username' => $username,
            'full_name' => trim((string)$request->input('full_name')),
            'email' => $email,
            'phone' => trim((string)$request->input('phone')),
            'password_hash' => $passwordHash,
            'user_type' => $request->input('user_type', 'staff'),
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        // Sync roles
        $selectedRoles = $request->input('roles', []);
        if (is_array($selectedRoles) && !empty($selectedRoles)) {
            $this->userModel->syncRoles($userId, $selectedRoles, auth()->id());
        }

        // Audit log
        AuditLog::log('CREATE_USER', 'users', $userId, null, [
            'username' => $username,
            'email' => $email,
            'roles' => $selectedRoles,
        ]);

        Session::flash('success', 'تم إنشاء المستخدم وتعيين الأدوار بنجاح.');
        $this->redirect(url('/users'));
    }

    public function edit(Request $request, Response $response, string $id): string
    {
        if (!can('users.edit')) {
            Session::flash('error', 'ليس لديك صلاحية تعديل بيانات المستخدمين.');
            $this->redirect(url('/users'));
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            Session::flash('error', 'المستخدم المطلوب غير موجود.');
            $this->redirect(url('/users'));
        }

        $roles = $this->roleModel->all('id', 'ASC');
        $userRoles = $this->userModel->roles($id);
        $userRoleIds = array_column($userRoles, 'id');

        return $this->view('users/edit', [
            'pageTitle' => 'تعديل المستخدم: ' . $user['full_name'],
            'user' => $user,
            'roles' => $roles,
            'userRoleIds' => $userRoleIds,
        ]);
    }

    public function update(Request $request, Response $response, string $id): void
    {
        if (!can('users.edit')) {
            Session::flash('error', 'غير مصرح.');
            $this->redirect(url('/users'));
            return;
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            Session::flash('error', 'المستخدم غير موجود.');
            $this->redirect(url('/users'));
            return;
        }

        $validation = $this->validate($request->all(), [
            'full_name' => 'required|min:3',
            'email' => 'required|email',
        ]);

        if (!$validation['isValid']) {
            $this->redirect(url("/users/{$id}/edit"));
            return;
        }

        $updateData = [
            'full_name' => trim((string)$request->input('full_name')),
            'email' => trim((string)$request->input('email')),
            'phone' => trim((string)$request->input('phone')),
            'is_active' => $request->has('is_active') ? 1 : 0,
        ];

        // If new password provided
        $newPass = $request->input('new_password');
        if (!empty($newPass)) {
            if (strlen((string)$newPass) < 8) {
                Session::flash('error', 'يجب ألا تقل كلمة المرور الجديدة عن 8 أحرف.');
                $this->redirect(url("/users/{$id}/edit"));
                return;
            }
            $updateData['password_hash'] = password_hash((string)$newPass, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $this->userModel->update($id, $updateData);

        // Sync roles
        $selectedRoles = $request->input('roles', []);
        if (is_array($selectedRoles)) {
            $this->userModel->syncRoles((int)$id, $selectedRoles, auth()->id());
        }

        // Audit log
        AuditLog::log('UPDATE_USER', 'users', $id, $user, $updateData);

        Session::flash('success', 'تم تحديث بيانات المستخدم بنجاح.');
        $this->redirect(url('/users'));
    }

    public function toggleStatus(Request $request, Response $response, string $id): void
    {
        if (!can('users.delete')) {
            Session::flash('error', 'غير مصرح بتعديل حالة المستخدم.');
            $this->redirect(url('/users'));
            return;
        }

        $user = $this->userModel->find($id);
        if ($user) {
            // Cannot deactivate yourself
            if ((int)$user['id'] === auth()->id()) {
                Session::flash('error', 'لا يمكنك تعطيل حسابك الشخصي الذي تستخدمه حالياً.');
                $this->redirect(url('/users'));
                return;
            }

            $newStatus = $user['is_active'] ? 0 : 1;
            $this->userModel->update($id, ['is_active' => $newStatus]);

            AuditLog::log('TOGGLE_USER_STATUS', 'users', $id, ['is_active' => $user['is_active']], ['is_active' => $newStatus]);

            $msg = $newStatus ? 'تم تفعيل الحساب بنجاح.' : 'تم تعطيل الحساب بنجاح.';
            Session::flash('success', $msg);
        }

        $this->redirect(url('/users'));
    }
}
