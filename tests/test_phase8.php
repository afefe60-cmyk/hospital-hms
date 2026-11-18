<?php

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');
$db = \App\Core\Database::getInstance();

echo "=========================================================\n";
echo " HOSPITAL HMS - PHASE 8 PHARMACY & INVENTORY (FEFO) TESTS\n";
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

// 1. Medicine Catalog Verification
$medModel = new \App\Models\Medicine();
$pcm = $medModel->firstWhere('code', 'MED-PCM-500');
assertTest("Paracetamol 500mg exists in medicine catalog", $pcm !== null && $pcm['dosage_form'] === 'tablet');

$coartem = $medModel->firstWhere('code', 'MED-COARTEM');
assertTest("Coartem antimalarial exists in catalog", $coartem !== null);

$cef = $medModel->firstWhere('code', 'MED-CEF-1G');
assertTest("Ceftriaxone 1g injection exists in catalog", $cef !== null && $cef['dosage_form'] === 'injection');

// 2. Warehouses Verification
$whModel = new \App\Models\Warehouse();
$mainWh = $whModel->firstWhere('code', 'WH-MAIN');
assertTest("Central Medical Store (WH-MAIN) exists", $mainWh !== null && $mainWh['type'] === 'main');

$pharmWh = $whModel->firstWhere('code', 'WH-PHARM');
assertTest("OPD Pharmacy (WH-PHARM) exists", $pharmWh !== null && $pharmWh['type'] === 'pharmacy');

// 3. FEFO (First Expired, First Out) Ordering Test
if ($pcm) {
    $batches = $medModel->activeBatches((int)$pcm['id']);
    assertTest("Paracetamol has active batches", count($batches) >= 2);

    // Verify FEFO: First batch expiry must be earlier than second batch expiry
    $isFefo = strtotime($batches[0]['expiry_date']) <= strtotime($batches[1]['expiry_date']);
    assertTest("FEFO Protocol: Earliest expiring batch is prioritized first", $isFefo);
}

// 4. Code Generators
$poModel = new \App\Models\PurchaseOrder();
$poNum = $poModel->generatePoNumber();
assertTest("Purchase Order number format (PO-YYYYMM-XXXX)", (bool)preg_match('/^PO-\d{6}-\d{4}$/', $poNum));

$dspModel = new \App\Models\PharmacyDispense();
$dspNum = $dspModel->generateDispenseNumber();
assertTest("Dispense number format (DSP-YYYYMMDD-XXXX)", (bool)preg_match('/^DSP-\d{8}-\d{4}$/', $dspNum));

$movModel = new \App\Models\StockMovement();
$movNum = $movModel->generateMovementNumber();
assertTest("Stock Movement number format (MOV-YYYYMMDD-XXXXX)", (bool)preg_match('/^MOV-\d{8}-\d{5}$/', $movNum));

// 5. Suppliers Verification
$supModel = new \App\Models\Supplier();
$nmsf = $supModel->firstWhere('code', 'SUP-NMSF');
assertTest("National Medical Supplies Fund (NMSF) exists as supplier", $nmsf !== null);

// 6. Seeded Purchase Order Verification
$seededPo = $poModel->firstWhere('po_number', 'PO-202611-0001');
assertTest("Seeded Purchase Order exists with 'received' status", $seededPo !== null && $seededPo['status'] === 'received');

if ($seededPo) {
    $poDetailed = $poModel->detailed((int)$seededPo['id']);
    assertTest("PO detailed includes supplier name and total amount in SDG", !empty($poDetailed['supplier_name']) && (float)$poDetailed['total_amount'] > 0);
}

// 7. Seeded Pharmacy Dispense Verification
$seededDsp = $dspModel->firstWhere('dispense_number', 'DSP-20261118-0001');
assertTest("Seeded Pharmacy Dispense exists", $seededDsp !== null);

if ($seededDsp) {
    $dspDetailed = $dspModel->detailed((int)$seededDsp['id']);
    assertTest("Dispense detailed includes patient name and pharmacist", !empty($dspDetailed['patient_name']) && !empty($dspDetailed['pharmacist_name']));

    $dspItems = $dspModel->items((int)$seededDsp['id']);
    assertTest("Dispense items retrieved with batch and medicine names", !empty($dspItems) && !empty($dspItems[0]['batch_number']));
}

// 8. Stock Movement Audit Logging Test
$movId = \App\Models\StockMovement::log(
    'purchase_receipt',
    $pcm['id'],
    $batches[0]['id'],
    $mainWh['id'],
    50,
    'test_ref',
    1,
    'اختبار حركة المخزون'
);
assertTest("Stock movement logged successfully", $movId > 0);

$loggedMov = $movModel->find($movId);
assertTest("Stock movement quantity and type verified", $loggedMov !== null && (int)$loggedMov['quantity'] === 50 && $loggedMov['movement_type'] === 'purchase_receipt');

echo "\n---------------------------------------------------------\n";
echo " PHASE 8 TEST RESULTS: {$testsPassed} / {$testsTotal} PASSED\n";
echo "---------------------------------------------------------\n";

exit($testsPassed === $testsTotal ? 0 : 1);
