<?php

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');
$db = \App\Core\Database::getInstance();

echo "===================================================================\n";
echo " HOSPITAL HMS - PHASE 11 HEALTH INSURANCE & CLAIMS UNIT TESTS\n";
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

// 1. Insurance Companies Test
$compModel = new \App\Models\InsuranceCompany();
$sheikan = $compModel->firstWhere('code', 'INS-SHEIKAN');
assertTest("Sheikan Insurance Co. (INS-SHEIKAN) exists", $sheikan !== null && (float)$sheikan['default_coverage_rate'] === 80.00);

$baraka = $compModel->firstWhere('code', 'INS-BARAKA');
assertTest("Al Baraka Insurance exists with 85% coverage", $baraka !== null && (float)$baraka['default_coverage_rate'] === 85.00);

$nhif = $compModel->firstWhere('code', 'INS-NHIF');
assertTest("National Health Insurance Fund (NHIF) exists", $nhif !== null);

$activeComps = $compModel->activeCompanies();
assertTest("Active companies count is at least 4", count($activeComps) >= 4);

// 2. Patient Insurance Policy Test
$polModel = new \App\Models\PatientInsurance();
$testPat = $db->fetchOne("SELECT id FROM patients ORDER BY id ASC LIMIT 1");
$activePol = $polModel->activePolicyForPatient((int)$testPat['id']);
assertTest("Patient 1 has active Sheikan insurance policy",
    $activePol !== null && $activePol['policy_number'] === 'SHK-POL-2026-9901' && (float)$activePol['coverage_percentage'] === 80.00);

// 3. Insurance Calculation Split Algorithm Test
$split1 = \App\Models\InsuranceClaim::calculateInsuranceSplit(100000.00, 80.00);
assertTest("Split Algorithm: 100,000 SDG at 80% => 80,000 SDG Insurance & 20,000 SDG Patient",
    $split1['insurance_claim'] === 80000.00 && $split1['patient_due'] === 20000.00);

$splitCopay = \App\Models\InsuranceClaim::calculateInsuranceSplit(100000.00, 80.00, 50000.00);
assertTest("Split Algorithm with Copay Limit (50,000 SDG) caps insurance claim",
    $splitCopay['insurance_claim'] === 50000.00 && $splitCopay['patient_due'] === 50000.00);

// 4. Claim Model & Number Generator Test
$claimModel = new \App\Models\InsuranceClaim();
$clmNum = $claimModel->generateClaimNumber();
assertTest("Claim number format (CLM-YYYYMM-XXXXX)", (bool)preg_match('/^CLM-\d{6}-\d{5}$/', $clmNum));

$seededClaim = $claimModel->firstWhere('claim_number', 'CLM-202611-00001');
assertTest("Seeded Insurance Claim exists", $seededClaim !== null);
if ($seededClaim) {
    $detailedClaim = $claimModel->detailed((int)$seededClaim['id']);
    assertTest("Claim detailed join includes patient info and MRN",
        !empty($detailedClaim['patient_name']) && !empty($detailedClaim['mrn']));
    assertTest("Claim detailed join includes insurance company name",
        !empty($detailedClaim['company_name_ar']));
    assertTest("Claim status is approved", $detailedClaim['status'] === 'approved');
    assertTest("Claim approved amount is positive in SDG", (float)$detailedClaim['approved_amount'] > 0);
}

// 5. Dynamic Claim Lifecycle & Status Update Test
$adminU = $db->fetchOne("SELECT id FROM users WHERE username = 'admin'");
$tempInv = $db->fetchOne("SELECT id, patient_id FROM invoices ORDER BY id DESC LIMIT 1");
$tempClmNum = 'CLM-TEST-' . substr(uniqid(), -6);

$tempClaimId = $claimModel->create([
    'claim_number' => $tempClmNum,
    'company_id' => $sheikan['id'],
    'patient_id' => $tempInv['patient_id'],
    'invoice_id' => $tempInv['id'],
    'policy_number' => 'SHK-TEST-POL',
    'claim_amount' => 50000.00,
    'status' => 'submitted',
    'submission_date' => date('Y-m-d'),
    'created_by' => $adminU['id'],
]);

$createdClaim = $claimModel->find($tempClaimId);
assertTest("Dynamic claim created with status submitted", $createdClaim['status'] === 'submitted');

// Update to partially approved with deduction
$claimModel->update($tempClaimId, [
    'status' => 'partially_approved',
    'approved_amount' => 40000.00,
    'rejected_amount' => 10000.00,
    'rejection_reason' => 'Non-covered specialized test charge',
]);

$updatedClaim = $claimModel->find($tempClaimId);
assertTest("Claim status updated to partially_approved", $updatedClaim['status'] === 'partially_approved');
assertTest("Approved amount is 40,000 SDG", (float)$updatedClaim['approved_amount'] === 40000.00);
assertTest("Rejection reason documented", !empty($updatedClaim['rejection_reason']));

// Clean up temporary test claim
$db->query("DELETE FROM insurance_claims WHERE id = :id", ['id' => $tempClaimId]);

echo "\n---------------------------------------------------------\n";
echo " PHASE 11 UNIT TESTS SUMMARY: {$testsPassed} / {$testsTotal} PASSED\n";
echo "=========================================================\n";

if ($testsPassed === $testsTotal) {
    exit(0);
} else {
    exit(1);
}
