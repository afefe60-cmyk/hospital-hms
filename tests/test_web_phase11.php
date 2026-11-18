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
echo " HOSPITAL HMS - PHASE 11 WEB VIEWS & CONTROLLER TESTS\n";
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

// 1. Insurance Dashboard View
$insCtrl = new \App\Controllers\InsuranceController();
$insIndexHtml = $insCtrl->index($req, $res);
assertTest("Insurance Dashboard view renders with metrics in SDG & claims table",
    str_contains($insIndexHtml, 'إدارة التأمين الطبي والمطالبات') &&
    str_contains($insIndexHtml, 'إجمالي المطالبات المرفوعة') &&
    str_contains($insIndexHtml, 'المبالغ المعتمدة من التأمين'));

// 2. Insurance Companies Directory View
$compHtml = $insCtrl->companies($req, $res);
assertTest("Insurance Companies directory renders with contracted companies & coverage rates",
    str_contains($compHtml, 'شركات التأمين الطبي المعتمدة') &&
    str_contains($compHtml, 'شركة شيكان للتأمين') &&
    str_contains($compHtml, 'نسبة التغطية الافتراضية'));

// 3. Patient Policies Directory View
$polHtml = $insCtrl->policies($req, $res);
assertTest("Patient Policies view renders with policy numbers and member IDs",
    str_contains($polHtml, 'وثائق وبطاقات التأمين للمرضى') &&
    str_contains($polHtml, 'SHK-POL-2026-9901') &&
    str_contains($polHtml, 'MEM-88410'));

// 4. Create Insurance Claim View
$createClaimHtml = $insCtrl->createClaim($req, $res);
assertTest("Create Insurance Claim view renders with invoice selector & split calculation",
    str_contains($createClaimHtml, 'رفع مطالبة تأمينية جديدة') &&
    str_contains($createClaimHtml, 'شركة التأمين المطالَبة') &&
    str_contains($createClaimHtml, 'المبلغ المطلوب من شركة التأمين'));

// 5. Show Insurance Claim Dossier View
$claim = (new \App\Models\InsuranceClaim())->firstWhere('claim_number', 'CLM-202611-00001');
$showClaimHtml = $insCtrl->showClaim($req, $res, (string)$claim['id']);
assertTest("Claim Dossier view renders with patient details, invoice items & review modal",
    str_contains($showClaimHtml, 'CLM-202611-00001') &&
    str_contains($showClaimHtml, 'تفاصيل الخدمات الطبية المشمولة بالمطالبة') &&
    str_contains($showClaimHtml, 'قرار شركة التأمين والتدقيق'));

// 6. Printable Insurance Claim Form View
$printClaimHtml = $insCtrl->printClaim($req, $res, (string)$claim['id']);
assertTest("Printable Claim Form renders with hospital header, breakdown & signatures",
    str_contains($printClaimHtml, 'استمارة مطالبة تأمين صحي') &&
    str_contains($printClaimHtml, 'مستشفى الأمل التخصصي') &&
    str_contains($printClaimHtml, 'CLM-202611-00001'));

// 7. Printable Batch Claims Statement View
$printBatchHtml = $insCtrl->printBatch($req, $res);
assertTest("Printable Batch Statement renders with tabular summary & totals in SDG",
    str_contains($printBatchHtml, 'كشف مطالبات تأمين صحي مجمع') &&
    str_contains($printBatchHtml, 'الإجمالي الكلي:') &&
    str_contains($printBatchHtml, 'المدير المالي للمستشفى'));

echo "\n---------------------------------------------------------\n";
echo " PHASE 11 WEB VIEWS TESTS SUMMARY: {$testsPassed} / {$testsTotal} PASSED\n";
echo "=========================================================\n";

if ($testsPassed === $testsTotal) {
    exit(0);
} else {
    exit(1);
}
