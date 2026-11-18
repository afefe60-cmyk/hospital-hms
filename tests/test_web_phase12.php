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
echo " HOSPITAL HMS - PHASE 12 WEB VIEWS & CONTROLLER TESTS\n";
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

$hrCtrl = new \App\Controllers\HrController();

// 1. HR Dashboard View
$indexHtml = $hrCtrl->index($req, $res);
assertTest("HR Dashboard renders with staff metrics and today's roster",
    str_contains($indexHtml, 'الموارد البشرية وجداول المناوبات والرواتب') &&
    str_contains($indexHtml, 'إجمالي الكادر النشط') &&
    str_contains($indexHtml, 'على رأس العمل اليوم') &&
    str_contains($indexHtml, 'جدول مناوبات اليوم'));

// 2. Staff Duty Rosters View
$rosterHtml = $hrCtrl->roster($req, $res);
assertTest("Duty Rosters view renders with shift matrix and filters",
    str_contains($rosterHtml, 'جدول المناوبات والورديات') &&
    str_contains($rosterHtml, 'جدول مناوبات الفترة المحددة') &&
    str_contains($rosterHtml, 'إضافة / جدولة مناوبة'));

// 3. Employee Leaves View
$leavesHtml = $hrCtrl->leaves($req, $res);
assertTest("Leaves management view renders with request modal and status tabs",
    str_contains($leavesHtml, 'إدارة الإجازات والأذونات') &&
    str_contains($leavesHtml, 'سجل طلبات الإجازات') &&
    str_contains($leavesHtml, 'تقديم طلب إجازة جديد'));

// 4. Monthly Payroll Master View
$payrollHtml = $hrCtrl->payroll($req, $res);
assertTest("Payroll Master view renders with financial KPIs in SDG & payroll sheets",
    str_contains($payrollHtml, 'مسيرات الرواتب الشهرية والبدلات') &&
    str_contains($payrollHtml, 'إجمالي الرواتب الأساسية') &&
    str_contains($payrollHtml, 'إجمالي البدلات والحوافز') &&
    str_contains($payrollHtml, 'صافي الرواتب المستحقة') &&
    str_contains($payrollHtml, 'توليد مسير رواتب الشهر'));

// 5. Detailed Payslip View
$firstPayroll = (new \App\Models\Payroll())->firstWhere('salary_month', 11);
assertTest("First payroll record exists for payslip view", $firstPayroll !== null);

if ($firstPayroll) {
    $payslipHtml = $hrCtrl->showPayroll($req, $res, (string)$firstPayroll['id']);
    assertTest("Payslip detailed view renders with employee details & breakdown",
        str_contains($payslipHtml, 'كشف مفردات راتب موظف') &&
        str_contains($payslipHtml, 'الراتب الأساسي التعاقدي') &&
        str_contains($payslipHtml, 'البدلات والحوافز الإضافية') &&
        str_contains($payslipHtml, 'الاستقطاعات والخصومات الرسمية') &&
        str_contains($payslipHtml, 'صافي الراتب المستحق للصرف'));

    // 6. Printable Employee Payslip (A4)
    $printPayslipHtml = $hrCtrl->printPayslip($req, $res, (string)$firstPayroll['id']);
    assertTest("Printable Payslip renders without main layout and contains hospital header",
        str_contains($printPayslipHtml, 'كشف مفردات راتب شهري (Payslip)') &&
        str_contains($printPayslipHtml, 'مستشفى الأمل التخصصي - الخرطوم') &&
        str_contains($printPayslipHtml, 'توقيع واستلام الموظف'));
}

// 7. Printable Monthly Master Payroll Sheet
$printSheetHtml = $hrCtrl->printPayrollSheet($req, $res);
assertTest("Printable Master Payroll Sheet renders with landscape layout & totals row",
    str_contains($printSheetHtml, 'مسير الرواتب المجمع') &&
    str_contains($printSheetHtml, 'كشف مسير رواتب الكادر الطبي والإداري المجمع') &&
    str_contains($printSheetHtml, 'الإجمالي العام لمسير الرواتب (SDG)'));

echo "\n=========================================================\n";
echo " WEB VIEW TEST RESULTS: {$testsPassed} / {$testsTotal} PASSED\n";
echo "=========================================================\n";

exit($testsPassed === $testsTotal ? 0 : 1);
