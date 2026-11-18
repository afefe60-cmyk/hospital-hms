<?php

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');
$db = \App\Core\Database::getInstance();

echo "===================================================================\n";
echo " HOSPITAL HMS - PHASE 9 SURGERY (OR), ANESTHESIA & ICU UNIT TESTS\n";
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

// 1. Operating Rooms Test
$orRooms = $db->fetchAll("SELECT * FROM operating_rooms");
assertTest("Operating rooms exist in system", count($orRooms) >= 2);
$majorOr = null;
foreach ($orRooms as $r) {
    if (strpos($r['name_en'], 'Major') !== false) {
        $majorOr = $r;
        break;
    }
}
assertTest("Major Operating Theater exists", $majorOr !== null);

// 2. Surgery Model & Number Generator
$surgeryModel = new \App\Models\Surgery();
$surNum = $surgeryModel->generateSurgeryNumber();
assertTest("Surgery number generation matches SUR-YYYYMMDD-XXXX format", (bool)preg_match('/^SUR-\d{8}-\d{4}$/', $surNum));

// 3. Seeded Surgeries Verification
$sur1 = $surgeryModel->firstWhere('surgery_number', 'SUR-20261118-0001');
assertTest("Seeded Surgery 1 (SUR-20261118-0001) exists", $sur1 !== null);
if ($sur1) {
    $detailedSur1 = $surgeryModel->detailed((int)$sur1['id']);
    assertTest("Surgery 1 detailed join includes patient info", !empty($detailedSur1['patient_name']));
    assertTest("Surgery 1 detailed join includes lead surgeon", !empty($detailedSur1['lead_surgeon_name']));
    assertTest("Surgery 1 detailed join includes operating room", !empty($detailedSur1['or_name_ar']));
    assertTest("Surgery 1 status is completed", $detailedSur1['status'] === 'completed');
    assertTest("Surgery 1 procedure name recorded", strpos($detailedSur1['procedure_name'], 'Cholecystectomy') !== false);
}

// 4. Anesthesia Record Verification
$anesModel = new \App\Models\AnesthesiaRecord();
if ($sur1) {
    $anesRec = $anesModel->detailedForSurgery((int)$sur1['id']);
    assertTest("Anesthesia record exists for Surgery 1", $anesRec !== null);
    if ($anesRec) {
        assertTest("Anesthesia type is general", $anesRec['anesthesia_type'] === 'general');
        assertTest("ASA physical status is II", $anesRec['asa_physical_status'] === 'II');
        assertTest("Airway assessment Mallampati documented", strpos($anesRec['airway_assessment'], 'Mallampati') !== false);
        assertTest("Aldrete recovery score documented", strpos($anesRec['recovery_score'], 'Aldrete') !== false);
        assertTest("Anesthesiologist name is joined", !empty($anesRec['anesthesiologist_name']));
    }
}

// 5. Operating Room Conflict Detection Algorithm
if ($majorOr) {
    // Existing completed surgery 1 was 09:00 to 11:30. But conflict check only checks active (not completed/cancelled)
    // Let's test by scheduling an active surgery and verifying conflict
    $testDoc = $db->fetchOne("SELECT id FROM doctors LIMIT 1");
    $testPat = $db->fetchOne("SELECT id FROM patients LIMIT 1");
    $adminU = $db->fetchOne("SELECT id FROM users WHERE username = 'admin'");

    $uniqueSur = 'SUR-TEST-' . substr(uniqid(), -6);
    $startTest = '2026-11-19 10:00:00';
    $endTest = '2026-11-19 12:00:00';

    $tempId = $surgeryModel->create([
        'surgery_number' => $uniqueSur,
        'patient_id' => $testPat['id'],
        'operating_room_id' => $majorOr['id'],
        'lead_surgeon_id' => $testDoc['id'],
        'surgery_type' => 'elective',
        'procedure_name' => 'Test Procedure Conflict',
        'scheduled_start' => $startTest,
        'scheduled_end' => $endTest,
        'status' => 'scheduled',
        'created_by' => $adminU['id'],
    ]);

    // Check conflict overlapping 10:30 to 11:30 -> Should be true
    $hasConflict = $surgeryModel->checkOrConflict((int)$majorOr['id'], '2026-11-19 10:30:00', '2026-11-19 11:30:00');
    assertTest("OR Conflict Detection: Overlapping slot correctly flagged as conflict", $hasConflict === true);

    // Check non-overlapping slot 13:00 to 14:00 -> Should be false
    $noConflict = $surgeryModel->checkOrConflict((int)$majorOr['id'], '2026-11-19 13:00:00', '2026-11-19 14:00:00');
    assertTest("OR Conflict Detection: Free slot is correctly identified with no conflict", $noConflict === false);

    // Clean up temporary test surgery
    $db->query("DELETE FROM surgeries WHERE id = :id", ['id' => $tempId]);
}

// 6. ICU Model & Number Generator
$icuModel = new \App\Models\IcuStay();
$stayNum = $icuModel->generateStayNumber();
assertTest("ICU stay number generation matches ICU-YYYYMMDD-XXXX format", (bool)preg_match('/^ICU-\d{8}-\d{4}$/', $stayNum));

// 7. Seeded ICU Stay Verification
$icu1 = $icuModel->firstWhere('stay_number', 'ICU-20261118-0001');
assertTest("Seeded ICU stay (ICU-20261118-0001) exists", $icu1 !== null);
if ($icu1) {
    $detailedIcu = $icuModel->detailed((int)$icu1['id']);
    assertTest("ICU stay detailed join includes patient info", !empty($detailedIcu['patient_name']));
    assertTest("ICU stay detailed join includes bed and room", !empty($detailedIcu['bed_number']) && !empty($detailedIcu['room_number']));
    assertTest("ICU stay ventilator support is invasive", $detailedIcu['ventilator_status'] === 'invasive');
    assertTest("ICU stay APACHE score documented", (int)$detailedIcu['apache_score'] === 18);
}

// 8. ICU Observations Verification
$obsModel = new \App\Models\IcuObservation();
if ($icu1) {
    $observations = $obsModel->forStay((int)$icu1['id']);
    assertTest("ICU stay has at least 3 hourly observation records", count($observations) >= 3);
    if (!empty($observations)) {
        $firstObs = $observations[0];
        assertTest("Observation record contains systolic & diastolic BP", !empty($firstObs['systolic_bp']) && !empty($firstObs['diastolic_bp']));
        assertTest("Observation record contains GCS score", $firstObs['gcs_score'] >= 3 && $firstObs['gcs_score'] <= 15);
        assertTest("Observation record contains Ventilator Mode and FiO2", !empty($firstObs['ventilator_mode']) && !empty($firstObs['fio2']));
        assertTest("Observation record contains Urine Output and IV Fluids", isset($firstObs['urine_output_ml']) && isset($firstObs['iv_fluids_ml']));
    }
}

echo "\n---------------------------------------------------------\n";
echo " PHASE 9 UNIT TESTS SUMMARY: {$testsPassed} / {$testsTotal} PASSED\n";
echo "=========================================================\n";

if ($testsPassed === $testsTotal) {
    exit(0);
} else {
    exit(1);
}
