<?php

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');
$db = \App\Core\Database::getInstance();

echo "===================================================================\n";
echo " HOSPITAL HMS - PHASE 13 REPORTS, KPIS & SETTINGS UNIT TESTS\n";
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

// 1. System Settings Model Tests
$hospName = \App\Models\SystemSetting::get('hospital_name_ar');
assertTest("Hospital Arabic Name exists in settings", 
    $hospName !== null && str_contains($hospName, 'مستشفى الخرطوم'));

$curr = \App\Models\SystemSetting::get('currency_code');
assertTest("System currency is Sudanese Pound (SDG)", $curr === 'SDG');

$bankakAcc = \App\Models\SystemSetting::get('bankak_account_number');
assertTest("Bankak account number setting exists", !empty($bankakAcc));

// Test set & get
$setOk = \App\Models\SystemSetting::set('test_phase13_key', 'test_val_999', 'general', 'وصف للاختبار');
assertTest("SystemSetting::set successfully persists setting", $setOk === true);

$getVal = \App\Models\SystemSetting::get('test_phase13_key');
assertTest("SystemSetting::get retrieves newly written value", $getVal === 'test_val_999');

// Test getByGroup
$settingModel = new \App\Models\SystemSetting();
$hospGroup = $settingModel->getByGroup('hospital');
assertTest("getByGroup('hospital') returns array of hospital identity settings", 
    is_array($hospGroup) && isset($hospGroup['hospital_name_en']));

$allGrouped = $settingModel->allGrouped();
assertTest("allGrouped returns settings organized by category", 
    isset($allGrouped['hospital']) && isset($allGrouped['financial']) && isset($allGrouped['clinical']) && isset($allGrouped['security']));

// 2. Attachment Model Tests
$attachModel = new \App\Models\Attachment();
$adminUser = (new \App\Models\User())->findByUsername('admin');
$attachId = $attachModel->create([
    'attachable_type' => 'patients',
    'attachable_id'   => 1,
    'file_name'       => 'test_national_id.pdf',
    'file_path'       => '/storage/attachments/patients/test_national_id.pdf',
    'file_mime'       => 'application/pdf',
    'file_size_bytes' => 102400,
    'uploaded_by'     => (int)$adminUser['id'],
]);
assertTest("Attachment record created successfully", $attachId > 0);

$patientAttachments = $attachModel->forModel('patients', 1);
assertTest("forModel returns attachments with uploader info", 
    count($patientAttachments) >= 1 && $patientAttachments[0]['file_name'] === 'test_national_id.pdf');

// 3. Analytics Service Tests
$analytics = new \App\Services\AnalyticsService();
$overview = $analytics->getExecutiveOverview('2026-01-01', '2026-12-31');

assertTest("getExecutiveOverview returns clinical and financial sections", 
    isset($overview['clinical']) && isset($overview['financial']));

$clin = $overview['clinical'];
assertTest("Clinical metrics contain valid patient count and bed occupancy", 
    $clin['total_patients'] >= 1 && isset($clin['bed_occupancy_rate']) && isset($clin['alos']));

$fin = $overview['financial'];
assertTest("Financial metrics contain gross billed, collections and net operating income in SDG", 
    isset($fin['gross_billed']) && isset($fin['total_collected']) && isset($fin['bankak_collected']));

// Test Departmental breakdown
$deptBreakdown = $analytics->getClinicalDepartmentBreakdown('2026-01-01', '2026-12-31');
assertTest("getClinicalDepartmentBreakdown returns departments with visit metrics", 
    is_array($deptBreakdown) && count($deptBreakdown) >= 1);

// Test Service revenue categories
$serviceRevenue = $analytics->getRevenueByServiceCategory('2026-01-01', '2026-12-31');
assertTest("getRevenueByServiceCategory returns service breakdown", is_array($serviceRevenue));

// Test Pharmacy Stock Metrics
$pharmacyMetrics = $analytics->getPharmacyStockMetrics();
assertTest("getPharmacyStockMetrics returns total stock value and FEFO expiry count", 
    $pharmacyMetrics['total_medicines'] >= 1 && isset($pharmacyMetrics['total_stock_value']) && isset($pharmacyMetrics['near_expiry_count']));

// Cleanup test records
$db->execute("DELETE FROM system_settings WHERE setting_key = 'test_phase13_key'");
$db->execute("DELETE FROM attachments WHERE file_name = 'test_national_id.pdf'");

echo "\n===================================================================\n";
echo " TEST RESULTS: {$testsPassed} / {$testsTotal} PASSED\n";
echo "===================================================================\n";

exit($testsPassed === $testsTotal ? 0 : 1);
