<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;

class RoleController extends Controller
{
    private Role $roleModel;
    private Permission $permissionModel;

    public function __construct()
    {
        $this->roleModel = new Role();
        $this->permissionModel = new Permission();
    }

    public function index(Request $request, Response $response): string
    {
        if (!can('roles.manage')) {
            Session::flash('error', 'ليس لديك صلاحية إدارة الأدوار والصلاحيات.');
            $this->redirect(url('/'));
        }

        $roles = $this->roleModel->all('id', 'ASC');

        // Add user count to each role
        foreach ($roles as &$role) {
            $role['user_count'] = $this->roleModel->userCount((int)$role['id']);
        }

        return $this->view('roles/index', [
            'pageTitle' => 'إدارة الأدوار والمجموعات الوظيفية (RBAC)',
            'roles' => $roles,
        ]);
    }

    public function permissions(Request $request, Response $response, string $id): string
    {
        if (!can('roles.manage')) {
            Session::flash('error', 'غير مصرح.');
            $this->redirect(url('/roles'));
        }

        $role = $this->roleModel->find($id);
        if (!$role) {
            Session::flash('error', 'الدور المطلوب غير موجود.');
            $this->redirect(url('/roles'));
        }

        $groupedPermissions = $this->permissionModel->allGroupedByModule();
        $rolePermissionIds = $this->roleModel->permissionIds((int)$id);

        return $this->view('roles/permissions', [
            'pageTitle' => 'صلاحيات الدور: ' . $role['display_name_ar'],
            'role' => $role,
            'groupedPermissions' => $groupedPermissions,
            'rolePermissionIds' => $rolePermissionIds,
        ]);
    }

    public function updatePermissions(Request $request, Response $response, string $id): void
    {
        if (!can('roles.manage')) {
            Session::flash('error', 'غير مصرح.');
            $this->redirect(url('/roles'));
            return;
        }

        $role = $this->roleModel->find($id);
        if (!$role) {
            Session::flash('error', 'الدور غير موجود.');
            $this->redirect(url('/roles'));
            return;
        }

        $permissionIds = $request->input('permissions', []);
        $permissionIds = is_array($permissionIds) ? array_map('intval', $permissionIds) : [];

        $oldPermissionIds = $this->roleModel->permissionIds((int)$id);
        $this->roleModel->syncPermissions((int)$id, $permissionIds);

        AuditLog::log('UPDATE_ROLE_PERMISSIONS', 'roles', $id, [
            'role' => $role['name'],
            'old_permissions' => $oldPermissionIds,
        ], [
            'role' => $role['name'],
            'new_permissions' => $permissionIds,
        ]);

        Session::flash('success', 'تم تحديث صلاحيات الدور بنجاح.');
        $this->redirect(url("/roles/{$id}/permissions"));
    }
}
