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
echo " HOSPITAL HMS - PHASE 10 WEB VIEWS & CONTROLLER TESTS\n";
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

// 1. Billing Dashboard View
$billCtrl = new \App\Controllers\BillingController();
$billIndexHtml = $billCtrl->index($req, $res);
assertTest("Billing Dashboard view renders with metrics in SDG & invoices table",
    str_contains($billIndexHtml, 'الفوترة الطبية والتحصيل') &&
    str_contains($billIndexHtml, 'فواتير اليوم الصادرة') &&
    str_contains($billIndexHtml, 'تحصيلات بنكك (Bankak)'));

// 2. Invoice Create View
$billCreateHtml = $billCtrl->create($req, $res);
assertTest("Invoice Create view renders with interactive services table & calculation elements",
    str_contains($billCreateHtml, 'إصدار فاتورة علاجية جديدة') &&
    str_contains($billCreateHtml, 'الخدمة الطبية (من الدليل)') &&
    str_contains($billCreateHtml, 'الصافي المستحق على المريض'));

// 3. Invoice Show View
$invoice = (new \App\Models\Invoice())->firstWhere('invoice_number', 'INV-202611-00001');
$billShowHtml = $billCtrl->show($req, $res, (string)$invoice['id']);
assertTest("Invoice Show view renders with line items, financial summary and payment modal",
    str_contains($billShowHtml, 'INV-202611-00001') &&
    str_contains($billShowHtml, 'بنود الخدمات الطبية والعلاجية') &&
    str_contains($billShowHtml, 'سجل التحصيل وسندات القبض'));

// 4. Invoice Printable View
$billPrintHtml = $billCtrl->printInvoice($req, $res, (string)$invoice['id']);
assertTest("Invoice Printable Bill renders with hospital header, breakdown and signatures",
    str_contains($billPrintHtml, 'مستشفى الأمل التخصصي') &&
    str_contains($billPrintHtml, 'فاتورة علاجية رسمية') &&
    str_contains($billPrintHtml, 'INV-202611-00001'));

// 5. Payment Receipt Printable View
$payment = (new \App\Models\Payment())->firstWhere('receipt_number', 'RCP-202611-00001');
$receiptPrintHtml = $billCtrl->printReceipt($req, $res, (string)$payment['id']);
assertTest("Payment Printable Receipt renders with Bankak reference and stamp box",
    str_contains($receiptPrintHtml, 'سند قبض مالي (Receipt)') &&
    str_contains($receiptPrintHtml, 'TRX-BOC-9988221') &&
    str_contains($receiptPrintHtml, 'RCP-202611-00001'));

// 6. Medical Services Price List View
$servicesHtml = $billCtrl->services($req, $res);
assertTest("Medical Services Price List renders with SDG prices and department badges",
    str_contains($servicesHtml, 'دليل أسعار الخدمات الطبية (Service Catalog)') &&
    str_contains($servicesHtml, 'كشف ومناظرة استشاري') &&
    str_contains($servicesHtml, 'استئصال المرارة بالمنظار'));

// 7. Cashboxes Dashboard View
$cashCtrl = new \App\Controllers\CashboxController();
$cashIndexHtml = $cashCtrl->index($req, $res);
assertTest("Cashbox Dashboard view renders with cashboxes, active shifts and history",
    str_contains($cashIndexHtml, 'ورديات ونقاط الصناديق (Cashier Shifts)') &&
    str_contains($cashIndexHtml, 'صندوق الاستقبال والعيادات الخارجية') &&
    str_contains($cashIndexHtml, 'الورديات المفتوحة حالياً'));

// 8. Cashbox Session Show View
$session = (new \App\Models\CashboxSession())->firstWhere('session_number', 'SESS-20261118-0001');
$sessShowHtml = $cashCtrl->showSession($req, $res, (string)$session['id']);
assertTest("Cashbox Session Show view renders with collection breakdown (Cash, Bankak, Card)",
    str_contains($sessShowHtml, 'SESS-20261118-0001') &&
    str_contains($sessShowHtml, 'متحصلات بنكك (Bankak)') &&
    str_contains($sessShowHtml, 'سجل العمليات والمقبوضات خلال الوردية'));

// 9. Cashbox Session Print View
$sessPrintHtml = $cashCtrl->printSession($req, $res, (string)$session['id']);
assertTest("Cashbox Session Printable Report renders with reconciliation summary and signatures",
    str_contains($sessPrintHtml, 'محضر إغلاق ومطابقة وردية صندوق') &&
    str_contains($sessPrintHtml, 'متحصلات نقداً (كاش)') &&
    str_contains($sessPrintHtml, 'اعتماد الإدارة المالية'));

echo "\n---------------------------------------------------------\n";
echo " PHASE 10 WEB VIEWS TESTS SUMMARY: {$testsPassed} / {$testsTotal} PASSED\n";
echo "=========================================================\n";

if ($testsPassed === $testsTotal) {
    exit(0);
} else {
    exit(1);
}
