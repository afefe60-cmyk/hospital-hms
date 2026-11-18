<?php

$basePath = dirname(__DIR__);

// 1. Test Login View Render
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/login';
$_SERVER['HTTP_ACCEPT'] = 'text/html';

ob_start();
require $basePath . '/public/index.php';
$output = ob_get_clean();

$hasLoginTitle = str_contains($output, 'تسجيل الدخول');
$hasCsrf = str_contains($output, '_csrf_token');
$hasUsernameField = str_contains($output, 'name="username"');

echo "LOGIN_VIEW_TEST: " . ($hasLoginTitle && $hasCsrf && $hasUsernameField ? "PASS" : "FAIL") . "\n";

// 2. Test Login Authentication and Session Creation
$token = \App\Core\Session::token();
$_POST = [
    '_csrf_token' => $token,
    'username' => 'admin',
    'password' => 'Password@123',
];
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/login';

ob_start();
try {
    $router = \App\Core\App::getInstance()->getRouter();
    $req = new \App\Core\Request();
    $res = new \App\Core\Response();
    // Dispatch
    $authController = new \App\Controllers\AuthController();
    $authController->login($req, $res);
} catch (\Throwable $e) {
    // Redirect throws exit or sets header
}
$output = ob_get_clean();

echo "LOGIN_PROCESS_TEST: " . (auth()->check() ? "PASS (User Logged In: " . auth()->user()['username'] . ")" : "FAIL") . "\n";

// 3. Test Users List View Render for Authenticated Admin
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/users';
$_SERVER['HTTP_ACCEPT'] = 'text/html';

$userController = new \App\Controllers\UserController();
$usersHtml = $userController->index(new \App\Core\Request(), new \App\Core\Response());
$hasUserTable = str_contains($usersHtml, 'إدارة المستخدمين') && str_contains($usersHtml, 'admin');

echo "USERS_VIEW_TEST: " . ($hasUserTable ? "PASS" : "FAIL") . "\n";

// 4. Test Roles Matrix View Render
$roleController = new \App\Controllers\RoleController();
$rolesHtml = $roleController->index(new \App\Core\Request(), new \App\Core\Response());
$hasRolesTable = str_contains($rolesHtml, 'الأدوار والمجموعات الوظيفية') && str_contains($rolesHtml, 'super_admin');

echo "ROLES_VIEW_TEST: " . ($hasRolesTable ? "PASS" : "FAIL") . "\n";

// 5. Test Audit Logs View Render
$auditController = new \App\Controllers\AuditLogController();
$auditHtml = $auditController->index(new \App\Core\Request(), new \App\Core\Response());
$hasAuditTable = str_contains($auditHtml, 'سجل التدقيق والمراقبة');

echo "AUDIT_VIEW_TEST: " . ($hasAuditTable ? "PASS" : "FAIL") . "\n";
