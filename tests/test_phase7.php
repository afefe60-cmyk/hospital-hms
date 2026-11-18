<?php

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');
$db = \App\Core\Database::getInstance();

echo "=========================================================\n";
echo " HOSPITAL HMS - PHASE 7 LAB (LIS) & RADIOLOGY (RIS) TESTS\n";
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

// 1. Lab Tests Catalog Verification
$testModel = new \App\Models\LabTest();
$cbc = $testModel->firstWhere('code', 'LAB-CBC');
assertTest("Lab Test CBC exists in catalog", $cbc !== null && (float)$cbc['price'] > 0);

$malaria = $testModel->firstWhere('code', 'LAB-MAL-BF');
assertTest("Lab Test Malaria BF exists", $malaria !== null && $malaria['category'] === 'parasitology');

$creat = $testModel->firstWhere('code', 'LAB-CREAT');
assertTest("Lab Test Serum Creatinine exists with mg/dL unit", $creat !== null && $creat['unit'] === 'mg/dL');

// 2. Lab Order & Barcode Generators
$orderModel = new \App\Models\LabOrder();
$orderNum = $orderModel->generateOrderNumber();
assertTest("Lab Order number format (LAB-YYYYMMDD-XXXX)", (bool)preg_match('/^LAB-\d{8}-\d{4}$/', $orderNum));

$sampleModel = new \App\Models\LabSample();
$barcode = $sampleModel->generateBarcode();
assertTest("Sample Barcode format (SMP-YYYYMMDD-XXXXX)", (bool)preg_match('/^SMP-\d{8}-\d{5}$/', $barcode));

// 3. Seeded Lab Order Verification
$seededLabOrder = $orderModel->firstWhere('order_number', 'LAB-20261118-0001');
assertTest("Seeded Lab Order LAB-20261118-0001 exists", $seededLabOrder !== null);

if ($seededLabOrder) {
    $detailed = $orderModel->detailed((int)$seededLabOrder['id']);
    assertTest("Lab Order detailed has patient name & MRN", !empty($detailed['patient_name']) && !empty($detailed['mrn']));

    $items = $orderModel->items((int)$seededLabOrder['id']);
    assertTest("Lab Order has test items", count($items) >= 2);

    $sample = $orderModel->sample((int)$seededLabOrder['id']);
    assertTest("Lab Order sample is tracked with barcode", $sample !== null && str_starts_with($sample['sample_barcode'], 'SMP-'));

    $result = $orderModel->result((int)$seededLabOrder['id']);
    assertTest("Lab Order result exists and is approved", $result !== null && $result['status'] === 'approved');

    if ($result) {
        $resultModel = new \App\Models\LabResult();
        $rItems = $resultModel->items((int)$result['id']);
        assertTest("Lab Result has granular parameters (Malaria, Hb, WBC, Platelets)", count($rItems) >= 4);

        $hasPositiveMalaria = false;
        foreach ($rItems as $ri) {
            if ($ri['flag'] === 'positive') {
                $hasPositiveMalaria = true;
                break;
            }
        }
        assertTest("Malaria positive flag identified", $hasPositiveMalaria);
    }
}

// 4. Radiology Services Catalog Verification
$radServiceModel = new \App\Models\RadiologyService();
$cxr = $radServiceModel->firstWhere('code', 'RAD-CXR-PA');
assertTest("Radiology Service Chest X-Ray exists (x_ray modality)", $cxr !== null && $cxr['modality'] === 'x_ray');

$ctBrain = $radServiceModel->firstWhere('code', 'RAD-CT-BRAIN');
assertTest("Radiology Service CT Brain exists (ct_scan modality)", $ctBrain !== null && $ctBrain['modality'] === 'ct_scan');

$echo = $radServiceModel->firstWhere('code', 'RAD-ECHO');
assertTest("Radiology Service Echocardiogram exists", $echo !== null && (float)$echo['price'] > 0);

// 5. Radiology Order Generator
$radOrderModel = new \App\Models\RadiologyOrder();
$radOrderNum = $radOrderModel->generateOrderNumber();
assertTest("Radiology Order number format (RAD-YYYYMMDD-XXXX)", (bool)preg_match('/^RAD-\d{8}-\d{4}$/', $radOrderNum));

// 6. Seeded Radiology Order Verification
$seededRadOrder = $radOrderModel->firstWhere('order_number', 'RAD-20261118-0001');
assertTest("Seeded Radiology Order RAD-20261118-0001 exists", $seededRadOrder !== null);

if ($seededRadOrder) {
    $radDetailed = $radOrderModel->detailed((int)$seededRadOrder['id']);
    assertTest("Radiology Order detailed has service name & patient info", !empty($radDetailed['service_name_ar']) && !empty($radDetailed['patient_name']));

    $report = $radOrderModel->report((int)$seededRadOrder['id']);
    assertTest("Radiology Report exists with findings & impression", $report !== null && !empty($report['findings']) && !empty($report['impression']));
    assertTest("Radiology Report is final and has radiologist assigned", $report !== null && $report['status'] === 'final' && !empty($report['radiologist_name']));
}

echo "\n---------------------------------------------------------\n";
echo " PHASE 7 TEST RESULTS: {$testsPassed} / {$testsTotal} PASSED\n";
echo "---------------------------------------------------------\n";

exit($testsPassed === $testsTotal ? 0 : 1);
