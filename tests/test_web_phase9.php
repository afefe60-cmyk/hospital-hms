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
echo " HOSPITAL HMS - PHASE 9 WEB VIEWS & CONTROLLER TESTS\n";
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

// 1. Surgery Dashboard View
$surgCtrl = new \App\Controllers\SurgeryController();
$surgIndexHtml = $surgCtrl->index($req, $res);
assertTest("Surgery Dashboard view renders with OR status & surgery table",
    str_contains($surgIndexHtml, 'إدارة العمليات الجراحية وغرف العمليات') &&
    str_contains($surgIndexHtml, 'حالة غرف العمليات والمسارح الجراحية') &&
    str_contains($surgIndexHtml, 'عمليات اليوم المجدولة'));

// 2. Surgery Booking / Create View
$surgCreateHtml = $surgCtrl->create($req, $res);
assertTest("Surgery Create view renders with booking form & team selectors",
    str_contains($surgCreateHtml, 'جدولة وحجز عملية جراحية') &&
    str_contains($surgCreateHtml, 'الجراح الرئيسي (Lead Surgeon)') &&
    str_contains($surgCreateHtml, 'طبيب التخدير (Anesthesiologist)'));

// 3. Surgery Detail & Show View
$surgery = (new \App\Models\Surgery())->firstWhere('surgery_number', 'SUR-20261118-0001');
$surgShowHtml = $surgCtrl->show($req, $res, (string)$surgery['id']);
assertTest("Surgery Show view renders with patient details, surgical team and anesthesia record",
    str_contains($surgShowHtml, 'SUR-20261118-0001') &&
    str_contains($surgShowHtml, 'الطاقم الجراحي والتخديري') &&
    str_contains($surgShowHtml, 'سجل التخدير الطبي (Anesthesia Record)'));

// 4. Surgery Printable Report View
$surgPrintHtml = $surgCtrl->printReport($req, $res, (string)$surgery['id']);
assertTest("Surgery Printable Report renders with header, operative findings and signatures",
    str_contains($surgPrintHtml, 'مستشفى الأمل التخصصي') &&
    str_contains($surgPrintHtml, 'سجل العملية والتخدير') &&
    str_contains($surgPrintHtml, 'توقيع الجراح الرئيسي'));

// 5. ICU Dashboard View
$icuCtrl = new \App\Controllers\IcuController();
$icuIndexHtml = $icuCtrl->index($req, $res);
assertTest("ICU Dashboard view renders with beds grid & active patients",
    str_contains($icuIndexHtml, 'وحدة العناية المركزة (ICU Dashboard)') &&
    str_contains($icuIndexHtml, 'خريطة توزيع ومصفوفة أسرة العناية المركزة') &&
    str_contains($icuIndexHtml, 'مرضى على التنفس الاصطناعي'));

// 6. ICU Admission / Create View
$icuCreateHtml = $icuCtrl->create($req, $res);
assertTest("ICU Create view renders with admission form & bed allocation",
    str_contains($icuCreateHtml, 'إدخال وتسكين مريض بالعناية المركزة') &&
    str_contains($icuCreateHtml, 'مقياس الخطورة (APACHE II)') &&
    str_contains($icuCreateHtml, 'دعم التنفس الاصطناعي'));

// 7. ICU Detail & Observation Chart View
$icuStay = (new \App\Models\IcuStay())->firstWhere('stay_number', 'ICU-20261118-0001');
$icuShowHtml = $icuCtrl->show($req, $res, (string)$icuStay['id']);
assertTest("ICU Show view renders with clinical monitor, fluid balance and flowsheet",
    str_contains($icuShowHtml, 'ICU-20261118-0001') &&
    str_contains($icuShowHtml, 'مخطط الملاحظات والمؤشرات الحيوية الدورية') &&
    str_contains($icuShowHtml, 'ميزان السوائل'));

// 8. ICU Printable Flowsheet Chart View
$icuPrintHtml = $icuCtrl->printChart($req, $res, (string)$icuStay['id']);
assertTest("ICU Printable Flowsheet renders with hourly observations table and signatures",
    str_contains($icuPrintHtml, 'مخطط الملاحظات الحيوية (ICU Flowsheet)') &&
    str_contains($icuPrintHtml, 'طبيب العناية المركزة') &&
    str_contains($icuPrintHtml, 'مسؤول تمريض العناية'));

echo "\n---------------------------------------------------------\n";
echo " PHASE 9 WEB VIEWS TESTS SUMMARY: {$testsPassed} / {$testsTotal} PASSED\n";
echo "=========================================================\n";

if ($testsPassed === $testsTotal) {
    exit(0);
} else {
    exit(1);
}
