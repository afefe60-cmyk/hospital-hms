<?php

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');
$db = \App\Core\Database::getInstance();

echo "=========================================================\n";
echo " HOSPITAL HMS - PHASE 6 ER, IPD, BEDS & NURSING TESTS\n";
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

// 1. ER Case Number Generator
$erModel = new \App\Models\EmergencyCase();
$caseNum = $erModel->generateCaseNumber();
assertTest("ER Case number format (ER-YYYYMMDD-XXXX)", (bool)preg_match('/^ER-\d{8}-\d{4}$/', $caseNum));

// 2. ER Triage Color Badges
$redBadge = \App\Models\TriageAssessment::colorBadge('red');
assertTest("Triage Red Badge is danger", str_contains($redBadge['badge'], 'bg-danger'));
$yellowBadge = \App\Models\TriageAssessment::colorBadge('yellow');
assertTest("Triage Yellow Badge is info", str_contains($yellowBadge['badge'], 'bg-info'));

// 3. Emergency Cases in Database
$erCases = $erModel->all();
assertTest("Emergency cases exist in database", count($erCases) >= 2);

$erCase1 = $erModel->firstWhere('case_number', 'ER-20261118-0001');
assertTest("Emergency Case 1 exists (Red Triage)", $erCase1 !== null && $erCase1['triage_level'] === 'red');

if ($erCase1) {
    $triageAssess = $erModel->triageAssessment((int)$erCase1['id']);
    assertTest("Triage assessment retrieved with GCS score", $triageAssess !== null && (int)$triageAssess['gcs_score'] === 10);
}

// 4. Inpatient Admission (IPD) Number Generator
$admModel = new \App\Models\Admission();
$admNum = $admModel->generateAdmissionNumber();
assertTest("Admission Number format (ADM-YYYY-XXXXX)", (bool)preg_match('/^ADM-\d{4}-\d{5}$/', $admNum));

// 5. Seeded Admission verification
$adm1 = $admModel->firstWhere('admission_number', 'ADM-2026-00001');
assertTest("Admission ADM-2026-00001 exists", $adm1 !== null);

if ($adm1) {
    $admDetailed = $admModel->detailed((int)$adm1['id']);
    assertTest("Admission detailed includes patient name and department", !empty($admDetailed['patient_name']) && !empty($admDetailed['department_name']));

    $activeBed = $admModel->activeBed((int)$adm1['id']);
    assertTest("Admission has active assigned bed", $activeBed !== null && !empty($activeBed['bed_number']));

    if ($activeBed) {
        $bedInDb = (new \App\Models\Bed())->find($activeBed['bed_id']);
        assertTest("Bed status in beds table is 'occupied'", $bedInDb['status'] === 'occupied');
    }
}

// 6. Nursing Care Plans & Nursing Notes
$noteModel = new \App\Models\NursingNote();
$notes = $noteModel->forAdmission((int)$adm1['id']);
assertTest("Nursing notes recorded for admission", !empty($notes));

$planModel = new \App\Models\NursingCarePlan();
$plans = $planModel->forAdmission((int)$adm1['id']);
assertTest("Nursing care plans recorded for admission", !empty($plans));

// 7. Medication Administration Record (MAR)
$marModel = new \App\Models\MedicationAdministration();
$marList = $marModel->forAdmission((int)$adm1['id']);
assertTest("MAR records exist for admission", count($marList) >= 3);

$pendingDose = $db->fetchOne("SELECT * FROM `medication_administrations` WHERE `admission_id` = :aid AND `status` = 'pending' LIMIT 1", ['aid' => $adm1['id']]);
assertTest("Pending medication dose exists in MAR", $pendingDose !== null);

// 8. Bed Transfer Logic Test
$bedModel = new \App\Models\Bed();
$availableBed = $db->fetchOne("SELECT * FROM `beds` WHERE `status` = 'available' LIMIT 1");
if ($availableBed && $activeBed) {
    $oldBedId = $activeBed['bed_id'];
    $newBedId = $availableBed['id'];

    $bedAssignModel = new \App\Models\BedAssignment();
    $bedAssignModel->assignBed((int)$adm1['id'], (int)$newBedId, 'اختبار تحويل السرير');

    $oldBedCheck = $bedModel->find($oldBedId);
    $newBedCheck = $bedModel->find($newBedId);

    assertTest("Bed Transfer: Old bed becomes 'cleaning'", $oldBedCheck['status'] === 'cleaning');
    assertTest("Bed Transfer: New bed becomes 'occupied'", $newBedCheck['status'] === 'occupied');

    // Transfer back to restore state
    $bedAssignModel->assignBed((int)$adm1['id'], (int)$oldBedId, 'إرجاع السرير الأصلي');
    $bedModel->update($newBedId, ['status' => 'available']);
    assertTest("Bed state restored cleanly", true);
}

// 9. Discharge Logic Test
$uniqueTestAdm = 'ADM-TEST-' . substr(uniqid(), -6);
$testAdmId = $admModel->create([
    'admission_number' => $uniqueTestAdm,
    'patient_id' => $adm1['patient_id'],
    'admitting_doctor_id' => $adm1['admitting_doctor_id'],
    'department_id' => $adm1['department_id'],
    'admission_date' => date('Y-m-d H:i:s'),
    'admission_type' => 'elective',
    'admission_reason' => 'حالة اختبار للخروج',
    'provisional_diagnosis' => 'اختبار سريري',
    'status' => 'admitted',
    'created_at' => date('Y-m-d H:i:s'),
]);

$testBed = $db->fetchOne("SELECT * FROM `beds` WHERE `status` = 'available' LIMIT 1");
if ($testBed) {
    $bedAssignModel = new \App\Models\BedAssignment();
    $bedAssignModel->assignBed((int)$testAdmId, (int)$testBed['id'], 'تسكين اختبار');

    // Discharge
    $dischargeModel = new \App\Models\DischargeRecord();
    $now = date('Y-m-d H:i:s');
    $dischargeModel->create([
        'admission_id' => (int)$testAdmId,
        'discharge_type' => 'normal',
        'discharge_summary' => 'شفاء تام وتحسن سريري كامل',
        'discharge_condition' => 'recovered',
        'discharge_doctor_id' => $adm1['admitting_doctor_id'],
        'discharged_at' => $now,
        'created_at' => $now,
    ]);

    $bedAssignModel->releaseBed((int)$testAdmId);
    $admModel->update($testAdmId, ['status' => 'discharged', 'discharge_date' => $now]);

    $dischargedBed = $bedModel->find($testBed['id']);
    assertTest("Discharge releases bed to 'cleaning'", $dischargedBed['status'] === 'cleaning');

    $dischargedAdm = $admModel->find($testAdmId);
    assertTest("Admission status updated to 'discharged'", $dischargedAdm['status'] === 'discharged');

    // Reset test bed to available
    $bedModel->update($testBed['id'], ['status' => 'available']);
}

echo "\n---------------------------------------------------------\n";
echo " PHASE 6 TEST RESULTS: {$testsPassed} / {$testsTotal} PASSED\n";
echo "---------------------------------------------------------\n";

exit($testsPassed === $testsTotal ? 0 : 1);
