<?php

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');

echo "==========================================\n";
echo " HOSPITAL HMS - PHASE 2 VERIFICATION TESTS\n";
echo "==========================================\n\n";

$passed = 0;
$failed = 0;

function assertTest(string $name, bool $condition, string $detail = '') {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$name}\n";
        $passed++;
    } else {
        echo " [FAIL] {$name} - {$detail}\n";
        $failed++;
    }
}

$db = \App\Core\Database::getInstance();

// 1. Tables check
$tables = array_column($db->fetchAll("SHOW TABLES"), 'Tables_in_hospital_hms');
assertTest("Users table exists", in_array('users', $tables));
assertTest("Roles table exists", in_array('roles', $tables));
assertTest("Permissions table exists", in_array('permissions', $tables));
assertTest("Audit logs table exists", in_array('audit_logs', $tables));
assertTest("Login logs table exists", in_array('login_logs', $tables));

// 2. Roles & Permissions seeded check
$rolesCount = (int)$db->fetchColumn("SELECT COUNT(*) FROM `roles`");
assertTest("15 Standard Roles Seeded", $rolesCount >= 15, "Found: {$rolesCount}");

$permsCount = (int)$db->fetchColumn("SELECT COUNT(*) FROM `permissions`");
assertTest("Standard Permissions Seeded", $permsCount >= 15, "Found: {$permsCount}");

// 3. Super admin user check
$admin = (new \App\Models\User())->findByUsername('admin');
assertTest("Super Admin account seeded", $admin !== null && $admin['username'] === 'admin');

// 4. Authentication logic tests
$auth = new \App\Services\AuthService();

// Test invalid login
$invalidResult = $auth->attempt('admin', 'wrong_password', '127.0.0.1', 'CLI-Tester');
assertTest("Failed login detected", $invalidResult['success'] === false);

// Test valid login
$validResult = $auth->attempt('admin', 'Password@123', '127.0.0.1', 'CLI-Tester');
assertTest("Valid login accepted", $validResult['success'] === true);
assertTest("Auth::check() returns true", $auth->check() === true);
assertTest("Auth::hasRole('super_admin') is true", $auth->hasRole('super_admin') === true);
assertTest("Auth::can('users.create') is true for super admin", $auth->can('users.create') === true);

// 5. Audit Log test
\App\Models\AuditLog::log('TEST_ACTION', 'test_table', 999, ['old' => 'val'], ['new' => 'val'], 1, '127.0.0.1');
$lastAudit = $db->fetchOne("SELECT * FROM `audit_logs` WHERE `action` = 'TEST_ACTION' ORDER BY id DESC LIMIT 1");
assertTest("Audit Log written with JSON data", $lastAudit !== null && $lastAudit['record_id'] == 999);

// 6. Logout test
$auth->logout();
assertTest("Auth::logout() destroys session", $auth->check() === false);

echo "\n------------------------------------------\n";
echo "Tests Completed: {$passed} Passed, {$failed} Failed\n";
echo "==========================================\n";

exit($failed > 0 ? 1 : 0);
