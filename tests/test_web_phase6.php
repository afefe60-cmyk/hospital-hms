<?php

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');

// Authenticate as admin
$admin = (new \App\Models\User())->findByUsername('admin');
\App\Core\Session::start();
\App\Core\Session::set('user_id', (int)$admin['id']);
\App\Core\Session::set('username', $admin['username']);
\App\Core\Session::set('full_name', $admin['full_name']);
\App\Core\Session::set('user_type', $admin['user_type']);

$req = new \App\Core\Request();
$res = new \App\Core\Response();

echo "=========================================================\n";
echo " HOSPITAL HMS - PHASE 6 WEB VIEWS & CONTROLLER TESTS\n";
echo "=========================================================\n\n";

$testsPassed = 0;
$testsTotal = 0;

function assertTest(string $description, bool $condition): void {
    global $testsPassed, $testsTotal;
    $testsTotal++;
    if ($condition) {
        $testsPassed++;
        echo "  [PASS] {$description}\n";
    } else {
        echo "  [FAIL] {$description}\n";
    }
}

// 1. Emergency Department Dashboard
$erCtrl = new \App\Controllers\EmergencyController();
$erHtml = $erCtrl->index($req, $res);
assertTest("Emergency Dashboard view renders with triage colors",
    str_contains($erHtml, 'قسم الحوادث والطوارئ') && str_contains($erHtml, 'إنعاش فوري') && str_contains($erHtml, 'طابور حالات الطوارئ'));

// 2. Emergency Case Dossier View
$erCase = (new \App\Models\EmergencyCase())->firstWhere('case_number', 'ER-20261118-0001');
$erShowHtml = $erCtrl->show($req, $res, (string)$erCase['id']);
assertTest("Emergency Case dossier renders with ABC & GCS",
    str_contains($erShowHtml, 'ER-20261118-0001') && str_contains($erShowHtml, 'مجرى الهواء') && str_contains($erShowHtml, 'مقياس غلاسكو للوعي'));

// 3. Inpatient Department (IPD) Dashboard View
$admCtrl = new \App\Controllers\AdmissionController();
$admHtml = $admCtrl->index($req, $res);
assertTest("Inpatient IPD Dashboard renders with admissions list",
    str_contains($admHtml, 'إدارة التنويم والأقسام الداخلية') && str_contains($admHtml, 'المرضى المنومين حالياً'));

// 4. Inpatient Single Admission Dossier View
$adm = (new \App\Models\Admission())->firstWhere('admission_number', 'ADM-2026-00001');
$admShowHtml = $admCtrl->show($req, $res, (string)$adm['id']);
assertTest("Admission dossier renders with active bed, MAR & Care Plan",
    str_contains($admShowHtml, 'ADM-2026-00001') && str_contains($admShowHtml, 'بيانات إشغال السرير الحالي') && str_contains($admShowHtml, 'سجل إعطاء الأدوية'));

// 5. Nursing Station & Central MAR Dashboard View
$nurseCtrl = new \App\Controllers\NursingController();
$nurseHtml = $nurseCtrl->index($req, $res);
assertTest("Nursing Station renders with active MAR schedule",
    str_contains($nurseHtml, 'محطة التمريض السريرية') && str_contains($nurseHtml, 'جدول الجرعات الدوائية المستحقة'));

echo "\n---------------------------------------------------------\n";
echo " PHASE 6 WEB TEST RESULTS: {$testsPassed} / {$testsTotal} PASSED\n";
echo "---------------------------------------------------------\n";

exit($testsPassed === $testsTotal ? 0 : 1);
