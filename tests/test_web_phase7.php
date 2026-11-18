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
echo " HOSPITAL HMS - PHASE 7 WEB VIEWS & CONTROLLER TESTS\n";
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

// 1. Lab Dashboard View
$labCtrl = new \App\Controllers\LabController();
$labHtml = $labCtrl->index($req, $res);
assertTest("Laboratory LIS Dashboard view renders with statistics",
    str_contains($labHtml, 'نظام المختبرات الطبية والتحاليل') && str_contains($labHtml, 'بانتظار العينة') && str_contains($labHtml, 'نتائج معتمدة مكتملة'));

// 2. Lab Single Order Dossier View
$labOrder = (new \App\Models\LabOrder())->firstWhere('order_number', 'LAB-20261118-0001');
$labShowHtml = $labCtrl->show($req, $res, (string)$labOrder['id']);
assertTest("Lab Order dossier renders with barcode and results",
    str_contains($labShowHtml, 'LAB-20261118-0001') && str_contains($labShowHtml, 'SMP-20261118-00001') && str_contains($labShowHtml, 'Malaria Blood Film'));

// 3. Lab Printable Report View
$labPrintHtml = $labCtrl->printReport($req, $res, (string)$labOrder['id']);
assertTest("Lab Printable Report renders with header and parameters",
    str_contains($labPrintHtml, 'مستشفى الأمل التخصصي') && str_contains($labPrintHtml, 'تقرير تحاليل مخبرية') && str_contains($labPrintHtml, 'Hemoglobin'));

// 4. Radiology Dashboard View
$radCtrl = new \App\Controllers\RadiologyController();
$radHtml = $radCtrl->index($req, $res);
assertTest("Radiology RIS Dashboard renders with modality statistics",
    str_contains($radHtml, 'نظام الأشعة والتصوير الطبي') && str_contains($radHtml, 'طلبات جديدة مطلوبة') && str_contains($radHtml, 'تقارير منجزة'));

// 5. Radiology Single Order Dossier View
$radOrder = (new \App\Models\RadiologyOrder())->firstWhere('order_number', 'RAD-20261118-0001');
$radShowHtml = $radCtrl->show($req, $res, (string)$radOrder['id']);
assertTest("Radiology Order dossier renders with findings & impression",
    str_contains($radShowHtml, 'RAD-20261118-0001') && str_contains($radShowHtml, 'تقرير استشاري الأشعة') && str_contains($radShowHtml, 'Bilateral lung fields appear clear'));

// 6. Radiology Printable Report View
$radPrintHtml = $radCtrl->printReport($req, $res, (string)$radOrder['id']);
assertTest("Radiology Printable Report renders with impressions and signature",
    str_contains($radPrintHtml, 'مستشفى الأمل التخصصي') && str_contains($radPrintHtml, 'تقرير أشعة تشخيصي') && str_contains($radPrintHtml, 'Normal Chest Radiograph'));

echo "\n---------------------------------------------------------\n";
echo " PHASE 7 WEB TEST RESULTS: {$testsPassed} / {$testsTotal} PASSED\n";
echo "---------------------------------------------------------\n";

exit($testsPassed === $testsTotal ? 0 : 1);
