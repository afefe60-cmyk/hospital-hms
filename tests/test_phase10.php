<?php

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');
$db = \App\Core\Database::getInstance();

echo "===================================================================\n";
echo " HOSPITAL HMS - PHASE 10 FINANCE, BILLING & CASHIER UNIT TESTS\n";
echo "===================================================================\n\n";

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

// 1. Chart of Accounts Test
$accModel = new \App\Models\Account();
$cashAcc = $accModel->firstWhere('account_code', '1101');
assertTest("Cash on hand account (1101) exists in Chart of Accounts", $cashAcc !== null && $cashAcc['account_type'] === 'asset');

$bankakAcc = $accModel->firstWhere('account_code', '1102');
assertTest("Bankak bank account (1102) exists", $bankakAcc !== null && $bankakAcc['account_type'] === 'asset');

$arPatientsAcc = $accModel->firstWhere('account_code', '1103');
assertTest("Accounts Receivable Patients account (1103) exists", $arPatientsAcc !== null);

$revOpdAcc = $accModel->firstWhere('account_code', '4101');
assertTest("OPD Consultation Revenue account (4101) exists", $revOpdAcc !== null && $revOpdAcc['account_type'] === 'revenue');

// 2. Cost Centers Test
$ccModel = new \App\Models\CostCenter();
$ccAdmin = $ccModel->firstWhere('code', 'CC-ADMIN');
assertTest("General Administration cost center (CC-ADMIN) exists", $ccAdmin !== null);

$ccIcu = $ccModel->firstWhere('code', 'CC-ICU');
assertTest("ICU cost center (CC-ICU) exists", $ccIcu !== null);

// 3. Service Catalog Test
$srvModel = new \App\Models\ServiceCatalog();
$srvCons = $srvModel->firstWhere('code', 'SRV-CONS-SPEC');
assertTest("Specialist Consultation service exists with standard price in SDG",
    $srvCons !== null && (float)$srvCons['price_standard'] === 25000.00);

$srvChol = $srvModel->firstWhere('code', 'SRV-SURG-LAP-CHOL');
assertTest("Laparoscopic Cholecystectomy surgical service exists",
    $srvChol !== null && (float)$srvChol['price_standard'] === 350000.00);

$srvCbc = $srvModel->firstWhere('code', 'SRV-LAB-CBC');
assertTest("CBC Lab Test service exists with price 8,000 SDG",
    $srvCbc !== null && (float)$srvCbc['price_standard'] === 8000.00);

// 4. Cashboxes & Sessions Test
$cbModel = new \App\Models\Cashbox();
$mainBox = $cbModel->firstWhere('code', 'CASH-REC');
assertTest("Reception Cashbox (CASH-REC) exists", $mainBox !== null);

$sessModel = new \App\Models\CashboxSession();
$sessNum = $sessModel->generateSessionNumber();
assertTest("Cashbox session number format (SESS-YYYYMMDD-XXXX)", (bool)preg_match('/^SESS-\d{8}-\d{4}$/', $sessNum));

$seededSess = $sessModel->firstWhere('session_number', 'SESS-20261118-0001');
assertTest("Seeded Cashbox Session 1 exists and is open", $seededSess !== null && $seededSess['status'] === 'open');

// 5. Invoice Model & Number Generator Test
$invModel = new \App\Models\Invoice();
$invNum = $invModel->generateInvoiceNumber();
assertTest("Invoice number format (INV-YYYYMM-XXXXX)", (bool)preg_match('/^INV-\d{6}-\d{5}$/', $invNum));

$seededInv = $invModel->firstWhere('invoice_number', 'INV-202611-00001');
assertTest("Seeded Invoice 1 exists", $seededInv !== null);
if ($seededInv) {
    $detailedInv = $invModel->detailed((int)$seededInv['id']);
    assertTest("Invoice detailed join includes patient info and phone",
        !empty($detailedInv['patient_name']) && !empty($detailedInv['mrn']));
    assertTest("Invoice status is paid", $detailedInv['status'] === 'paid');
    assertTest("Invoice total gross equals 37,000 SDG", (float)$detailedInv['total_gross'] === 37000.00);

    $items = $invModel->items((int)$seededInv['id']);
    assertTest("Invoice has 3 detailed line items", count($items) === 3);

    $payments = $invModel->payments((int)$seededInv['id']);
    assertTest("Invoice has payment receipt attached", count($payments) >= 1);
}

// 6. Payment Model & Receipt Generator Test
$payModel = new \App\Models\Payment();
$rcpNum = $payModel->generateReceiptNumber();
assertTest("Receipt number format (RCP-YYYYMM-XXXXX)", (bool)preg_match('/^RCP-\d{6}-\d{5}$/', $rcpNum));

$seededPay = $payModel->firstWhere('receipt_number', 'RCP-202611-00001');
assertTest("Seeded Payment receipt exists", $seededPay !== null);
if ($seededPay) {
    assertTest("Payment method is bankak", $seededPay['payment_method'] === 'bankak');
    assertTest("Payment transaction reference is documented", $seededPay['transaction_reference'] === 'TRX-BOC-9988221');
    assertTest("Payment amount is 37,000 SDG", (float)$seededPay['amount'] === 37000.00);
}

// 7. Dynamic Flow & Total Recalculation Test
$testPat = $db->fetchOne("SELECT id FROM patients LIMIT 1");
$adminU = $db->fetchOne("SELECT id FROM users WHERE username = 'admin'");
$tempInvNum = 'INV-TEST-' . substr(uniqid(), -6);

$tempInvId = $invModel->create([
    'invoice_number' => $tempInvNum,
    'patient_id' => $testPat['id'],
    'total_gross' => 50000.00,
    'total_discount' => 5000.00,
    'total_insurance' => 0.00,
    'total_patient_due' => 45000.00,
    'paid_amount' => 0.00,
    'status' => 'issued',
    'created_by' => $adminU['id'],
]);

$itemModel = new \App\Models\InvoiceItem();
$tempItemId = $itemModel->create([
    'invoice_id' => $tempInvId,
    'item_type' => 'service',
    'description' => 'Test Service Charge',
    'quantity' => 1,
    'unit_price' => 50000.00,
    'total_amount' => 50000.00,
]);

// Add partial payment of 20,000 SDG
$tempRcpNum = 'RCP-TEST-' . substr(uniqid(), -6);
$tempPayId = $payModel->create([
    'receipt_number' => $tempRcpNum,
    'invoice_id' => $tempInvId,
    'amount' => 20000.00,
    'payment_method' => 'cash',
    'collected_by' => $adminU['id'],
]);

$invModel->recalculateTotals($tempInvId);
$updatedTempInv = $invModel->find($tempInvId);
assertTest("Partial payment updates status to partially_paid", $updatedTempInv['status'] === 'partially_paid');
assertTest("Paid amount reflects 20,000 SDG", (float)$updatedTempInv['paid_amount'] === 20000.00);

// Add remaining payment of 25,000 SDG
$tempRcpNum2 = 'RCP-TEST-' . substr(uniqid(), -6);
$tempPayId2 = $payModel->create([
    'receipt_number' => $tempRcpNum2,
    'invoice_id' => $tempInvId,
    'amount' => 25000.00,
    'payment_method' => 'bankak',
    'collected_by' => $adminU['id'],
]);

$invModel->recalculateTotals($tempInvId);
$fullyPaidInv = $invModel->find($tempInvId);
assertTest("Full payment updates status to paid", $fullyPaidInv['status'] === 'paid');
assertTest("Total paid matches total patient due (45,000 SDG)", (float)$fullyPaidInv['paid_amount'] === 45000.00);

// Cleanup temporary test records
$db->query("DELETE FROM payments WHERE invoice_id = :id", ['id' => $tempInvId]);
$db->query("DELETE FROM invoice_items WHERE invoice_id = :id", ['id' => $tempInvId]);
$db->query("DELETE FROM invoices WHERE id = :id", ['id' => $tempInvId]);

echo "\n---------------------------------------------------------\n";
echo " PHASE 10 UNIT TESTS SUMMARY: {$testsPassed} / {$testsTotal} PASSED\n";
echo "=========================================================\n";

if ($testsPassed === $testsTotal) {
    exit(0);
} else {
    exit(1);
}
