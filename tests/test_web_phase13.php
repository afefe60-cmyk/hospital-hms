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
echo " HOSPITAL HMS - PHASE 13 WEB VIEWS & CONTROLLER TESTS\n";
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

$reportCtrl = new \App\Controllers\ReportController();
$settingCtrl = new \App\Controllers\SettingController();

// 1. Executive Analytics Dashboard View
$indexHtml = $reportCtrl->index($req, $res);
assertTest("Executive Analytics Dashboard renders with clinical & financial KPIs in SDG",
    str_contains($indexHtml, 'التقارير الإحصائية والتحليلية الشاملة') &&
    str_contains($indexHtml, 'المؤشرات الحيوية والتشغيلية') &&
    str_contains($indexHtml, 'الأداء المالي والتحصيل بالجنيه السوداني') &&
    str_contains($indexHtml, 'توزيع الزيارات والمراجعين حسب الأقسام'));

// 2. Financial & Revenue Report View
$finHtml = $reportCtrl->financial($req, $res);
assertTest("Financial Report renders with collections (Cash/Bankak) & invoices list",
    str_contains($finHtml, 'التقرير المالي والإيرادات التفصيلية') &&
    str_contains($finHtml, 'المتحصلات النقدية وعبر بنكك') &&
    str_contains($finHtml, 'فواتير الفترة المحددة') &&
    str_contains($finHtml, 'سندات التحصيل والمقبوضات بالفترة'));

// 3. Clinical & Inpatient Quality Report View
$clinHtml = $reportCtrl->clinical($req, $res);
assertTest("Clinical Report renders with Bed Occupancy, ALOS, admissions & surgeries",
    str_contains($clinHtml, 'تقرير المؤشرات السريرية والجودة الطبية') &&
    str_contains($clinHtml, 'معدل إشغال الأسرّة') &&
    str_contains($clinHtml, 'متوسط مدة الإقامة') &&
    str_contains($clinHtml, 'سجل حالات التنويم الداخلي'));

// 4. Pharmacy & Stock Valuation Report View
$invHtml = $reportCtrl->inventory($req, $res);
assertTest("Pharmacy & Stock report renders with stock valuation in SDG & FEFO batches",
    str_contains($invHtml, 'تقرير الصيدلية والمخزون الطبي') &&
    str_contains($invHtml, 'القيمة الإجمالية للمخزون (SDG)') &&
    str_contains($invHtml, 'أصناف وشيكة الانتهاء') &&
    str_contains($invHtml, 'نظام FEFO'));

// 5. Human Resources & Shift Coverage Report View
$hrHtml = $reportCtrl->hr($req, $res);
assertTest("Workforce HR Report renders with active staff and shift coverage analysis",
    str_contains($hrHtml, 'تقرير الكوادر وتغطية الورديات') &&
    str_contains($hrHtml, 'إجمالي الكادر النشط') &&
    str_contains($hrHtml, 'تحليل تغطية الورديات') &&
    str_contains($hrHtml, 'قائمة الكوادر النشطة'));

// 6. Official Printable Executive Hospital Report (A4)
$printHtml = $reportCtrl->printReport($req, $res);
assertTest("Printable Executive Report renders standalone with hospital header & signature blocks",
    str_contains($printHtml, 'التقرير التنفيذي والإحصائي الشامل') &&
    str_contains($printHtml, 'مستشفى الخرطوم التخصصي التعليمي') &&
    str_contains($printHtml, 'اعتماد المدير العام للمستشفى'));

// 7. System Settings View (Hospital Identity Tab)
$settingsHtml = $settingCtrl->index($req, $res);
assertTest("System Settings page renders with hospital identity and tabs",
    str_contains($settingsHtml, 'إعدادات النظام العامة وتخصيص هوية المستشفى') &&
    str_contains($settingsHtml, 'هوية وبيانات المستشفى') &&
    str_contains($settingsHtml, 'مستشفى الخرطوم التخصصي التعليمي'));

// 8. System Settings Financial Tab (Bankak Settings)
$_GET['tab'] = 'financial';
$reqFinancial = new \App\Core\Request();
$financialSettingsHtml = $settingCtrl->index($reqFinancial, $res);
assertTest("System Settings financial tab renders with Bankak account 1820499",
    str_contains($financialSettingsHtml, 'الإعدادات المالية وحسابات التحصيل الإلكتروني (بنكك)') &&
    str_contains($financialSettingsHtml, '1820499'));
unset($_GET['tab']);

echo "\n=========================================================\n";
echo " WEB VIEW TEST RESULTS: {$testsPassed} / {$testsTotal} PASSED\n";
echo "=========================================================\n";

exit($testsPassed === $testsTotal ? 0 : 1);
