<?php

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');
\App\Core\Session::start();

use App\Core\Request;
use App\Core\Response;
use App\Controllers\HomeController;
use App\Controllers\PatientController;
use App\Services\AuthService;
use App\Models\User;

echo "===================================================================\n";
echo " HOSPITAL HMS - UI/UX OVERHAUL & EXECUTIVE DASHBOARD TESTS\n";
echo "===================================================================\n\n";

$passCount = 0;
$totalTests = 0;

function assertTest(string $desc, bool $condition): void {
    global $passCount, $totalTests;
    $totalTests++;
    if ($condition) {
        $passCount++;
        echo "  [PASS] {$desc}\n";
    } else {
        echo "  [FAIL] {$desc}\n";
    }
}

// 1. Test HomeController renders data
$homeCtrl = new HomeController();
$req = new Request();
$res = new Response();

$content = $homeCtrl->index($req, $res);
assertTest('HomeController::index returns non-empty string', is_string($content) && strlen($content) > 500);
assertTest('Executive Banner contains Hospital Name', str_contains($content, 'مستشفى الأمل التخصصي') || str_contains($content, 'حساب بنكك'));
assertTest('Dashboard contains Quick Launchpad', str_contains($content, 'مسارات الوصول السريع') || str_contains($content, 'Quick Launchpad'));
assertTest('Dashboard contains Ward Capacity section', str_contains($content, 'Ward Capacity') || str_contains($content, 'إشغال الأجنحة'));
assertTest('Dashboard contains Surgical Theater schedule', str_contains($content, 'Surgical Theater') || str_contains($content, 'العمليات الجراحية'));
assertTest('Dashboard contains FEFO near-expiry alerts', str_contains($content, 'FEFO') || str_contains($content, 'صلاحيتها'));

// 2. Test Quick Search API
// Login as admin first
$userModel = new User();
$adminUser = $userModel->findByUsername('admin');
if ($adminUser) {
    \App\Core\Session::set('user_id', (int)$adminUser['id']);
    \App\Core\Session::set('username', $adminUser['username']);
    \App\Core\Session::set('full_name', $adminUser['full_name']);
    \App\Core\Session::set('user_type', $adminUser['user_type']);
}

$patientCtrl = new PatientController();
$_GET['q'] = 'عمر';
$searchReq = new Request();
$searchRes = new Response();
$searchResult = $patientCtrl->quickSearch($searchReq, $searchRes);
$searchData = json_decode($searchResult->getContent(), true);

assertTest('quickSearch returns JSON with data array', is_array($searchData) && isset($searchData['data']));

// Test short query returns empty data
$_GET['q'] = 'a';
$shortReq = new Request();
$shortRes = new Response();
$shortResult = $patientCtrl->quickSearch($shortReq, $shortRes);
$shortData = json_decode($shortResult->getContent(), true);
assertTest('quickSearch returns empty array when query is less than 2 characters', isset($shortData['data']) && count($shortData['data']) === 0);

// 3. Test Navbar Structure in layout
$layoutView = view('home', [
    'pageTitle' => 'لوحة القيادة',
    'hospital' => [
        'name_ar' => 'مستشفى الأمل التخصصي',
        'name_en' => 'Al-Amal Specialized Hospital',
        'phone' => '+249 183 770000',
        'emergency' => '999',
        'bankak_account' => '1820499',
        'currency' => 'SDG',
    ],
    'metrics' => [
        'total_patients' => 10,
        'today_visits' => 2,
        'active_inpatients' => 3,
        'total_beds' => 20,
        'occupied_beds' => 3,
        'available_beds' => 17,
        'bed_occupancy_rate' => 15.0,
        'today_surgeries' => 1,
        'today_er_cases' => 0,
        'today_lab_orders' => 0,
        'today_collections_sdg' => 50000,
        'today_bankak_sdg' => 30000,
        'today_cash_sdg' => 20000,
        'near_expiry_count' => 0,
        'stock_value_sdg' => 150000,
    ],
    'wardOccupancy' => [],
    'recentSurgeries' => [],
    'activeErCases' => [],
    'nearExpiryBatches' => [],
    'info' => [
        'app_name' => 'Hospital HMS',
        'php_version' => PHP_VERSION,
        'timezone' => 'Africa/Khartoum',
        'currency' => 'SDG',
    ],
]);

assertTest('Rendered layout contains organized dropdowns (الاستقبال وسجلات المرضى)', str_contains($layoutView, 'الاستقبال وسجلات المرضى'));
assertTest('Rendered layout contains Clinical dropdown (الخدمات السريرية والتنويم)', str_contains($layoutView, 'الخدمات السريرية والتنويم'));
assertTest('Rendered layout contains Diagnostics dropdown (التشخيص والصيدلية)', str_contains($layoutView, 'التشخيص والصيدلية'));
assertTest('Rendered layout contains Finance dropdown (المالية والتأمين)', str_contains($layoutView, 'المالية والتأمين'));
assertTest('Rendered layout contains HR dropdown (الموارد البشرية)', str_contains($layoutView, 'الموارد البشرية'));
assertTest('Rendered layout contains Admin dropdown (الإدارة والتقارير)', str_contains($layoutView, 'الإدارة والتقارير'));
assertTest('Rendered layout contains Quick Search Modal', str_contains($layoutView, 'quickSearchModal'));
assertTest('Rendered layout contains Ctrl+K shortcut listener', str_contains($layoutView, 'Ctrl+K'));

echo "\n===================================================================\n";
echo " TEST RESULTS: {$passCount} / {$totalTests} PASSED\n";
echo "===================================================================\n";

if ($passCount === $totalTests) {
    exit(0);
} else {
    exit(1);
}
