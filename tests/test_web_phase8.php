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
echo " HOSPITAL HMS - PHASE 8 WEB VIEWS & CONTROLLER TESTS\n";
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

// 1. Pharmacy Dashboard View
$pharmCtrl = new \App\Controllers\PharmacyController();
$pharmHtml = $pharmCtrl->index($req, $res);
assertTest("Pharmacy Dashboard view renders with pending prescriptions",
    str_contains($pharmHtml, 'إدارة الصيدلية وصرف الروشتات') && str_contains($pharmHtml, 'روشتات بانتظار الصرف'));

// 2. Pharmacy Dispense Screen View
$dispenseHtml = $pharmCtrl->dispense($req, $res);
assertTest("Pharmacy Dispense screen renders with FEFO batch selection",
    str_contains($dispenseHtml, 'شاشة صرف الأدوية والمستحضرات الطبية') && str_contains($dispenseHtml, 'الأقدم صلاحية أولاً - FEFO'));

// 3. Pharmacy Dispense Receipt View
$dispense = (new \App\Models\PharmacyDispense())->firstWhere('dispense_number', 'DSP-20261118-0001');
$receiptHtml = $pharmCtrl->receipt($req, $res, (string)$dispense['id']);
assertTest("Pharmacy Receipt view renders with items & patient info",
    str_contains($receiptHtml, 'DSP-20261118-0001') && str_contains($receiptHtml, 'إيصال صرف أدوية صيدلية') && str_contains($receiptHtml, 'المبلغ الإجمالي المدفوع'));

// 4. Pharmacy Printable Receipt View
$printReceiptHtml = $pharmCtrl->printReceipt($req, $res, (string)$dispense['id']);
assertTest("Pharmacy Printable Receipt renders with header and total",
    str_contains($printReceiptHtml, 'مستشفى الأمل التخصصي') && str_contains($printReceiptHtml, 'فاتورة صرف دواء نقدي') && str_contains($printReceiptHtml, 'DSP-20261118-0001'));

// 5. Inventory Dashboard View
$invCtrl = new \App\Controllers\InventoryController();
$invHtml = $invCtrl->index($req, $res);
assertTest("Inventory Dashboard renders with valuation & warehouse breakdown",
    str_contains($invHtml, 'المخازن والمستودعات الطبية') && str_contains($invHtml, 'إجمالي القيمة التقديرية للمخزون') && str_contains($invHtml, 'حالة المستودعات والمخازن'));

// 6. Medicines Catalog View
$medsHtml = $invCtrl->medicines($req, $res);
assertTest("Medicines Directory renders with categories and stock levels",
    str_contains($medsHtml, 'دليل الأدوية والمستحضرات الطبية') && str_contains($medsHtml, 'الاسم التجاري') && str_contains($medsHtml, 'Paracetamol'));

// 7. FEFO Batches Tracker View
$batchesHtml = $invCtrl->batches($req, $res);
assertTest("FEFO Batches Tracker renders with countdown days and priority",
    str_contains($batchesHtml, 'تتبع التشغيلات وتواريخ الصلاحية') && str_contains($batchesHtml, 'أولوية الصرف FEFO'));

// 8. Suppliers Directory View
$supHtml = $invCtrl->suppliers($req, $res);
assertTest("Suppliers Directory renders with contacts",
    str_contains($supHtml, 'دليل موردي الأدوية والمستلزمات الطبية') && str_contains($supHtml, 'الصندوق القومي للإمدادات الطبية'));

// 9. Purchase Orders View
$poHtml = $invCtrl->purchaseOrders($req, $res);
assertTest("Purchase Orders view renders with procurement list",
    str_contains($poHtml, 'أوامر الشراء والتوريد') && str_contains($poHtml, 'PO-202611-0001'));

echo "\n---------------------------------------------------------\n";
echo " PHASE 8 WEB TEST RESULTS: {$testsPassed} / {$testsTotal} PASSED\n";
echo "---------------------------------------------------------\n";

exit($testsPassed === $testsTotal ? 0 : 1);
