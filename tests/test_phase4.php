<?php

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');

echo "==========================================\n";
echo " HOSPITAL HMS - PHASE 4 VERIFICATION TESTS\n";
echo "==========================================\n\n";

$passed = 0;
$failed = 0;

function assertTest(string $name, bool $condition, string $detail = '') {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$name}\n";
        $passed++;
    } else {
        echo " [FAIL] {$name} - {$detail}\n";
        $failed++;
    }
}

$db = \App\Core\Database::getInstance();

// 1. Tables check
$tables = array_column($db->fetchAll("SHOW TABLES"), 'Tables_in_hospital_hms');
assertTest("Patients table exists", in_array('patients', $tables));
assertTest("Patient allergies table exists", in_array('patient_allergies', $tables));
assertTest("Patient conditions table exists", in_array('patient_conditions', $tables));
assertTest("Appointments table exists", in_array('appointments', $tables));
assertTest("Visits table exists", in_array('visits', $tables));
assertTest("Queues table exists", in_array('queues', $tables));

// 2. MRN Generator Test
$patientModel = new \App\Models\Patient();
$nextMrn = $patientModel->generateNextMrn();
assertTest("MRN Generator format (MRN-YYYY-XXXXXX)", (bool)preg_match('/^MRN-\d{4}-\d{6}$/', $nextMrn), "Generated: {$nextMrn}");

// 3. Patient Search & Directory Test
$searchResult = $patientModel->search('');
assertTest("Patients directory populated", $searchResult['total'] >= 2, "Found: {$searchResult['total']}");

$p1 = $patientModel->firstWhere('mrn', 'MRN-2026-000001');
assertTest("Patient P1 exists with Sudanese name", $p1 !== null && $p1['first_name'] === 'محمد');

// 4. Clinical Allergies Test
$allergies = $patientModel->allergies((int)$p1['id']);
assertTest("Patient P1 has recorded allergies", !empty($allergies));
if (!empty($allergies)) {
    assertTest("Penicillin allergy is life-threatening", $allergies[0]['severity'] === 'life_threatening');
}

// 5. Chronic Conditions Test
$conditions = $patientModel->conditions((int)$p1['id']);
assertTest("Patient P1 has chronic conditions", !empty($conditions));
if (!empty($conditions)) {
    assertTest("Diabetes condition has ICD-10 code E11", $conditions[0]['icd10_code'] === 'E11');
}

// 6. Appointment Token & Booking Test
$appointmentModel = new \App\Models\Appointment();
$token = $appointmentModel->generateToken(1, date('Y-m-d'));
assertTest("Token generated is numeric and positive", $token > 0);

// 7. Check-in and Visit Creation Test
$visitModel = new \App\Models\Visit();
$visitNumber = $visitModel->generateVisitNumber();
assertTest("Visit number format (VIS-YYYYMMDD-XXXXX)", (bool)preg_match('/^VIS-\d{8}-\d{5}$/', $visitNumber), "Generated: {$visitNumber}");

// 8. Queue Management Test
$queueModel = new \App\Models\Queue();
$clinicQueue = $queueModel->activeQueueForClinic(1);
assertTest("Active Queue for Clinic 1 retrieved", is_array($clinicQueue));

echo "\n------------------------------------------\n";
echo "Tests Completed: {$passed} Passed, {$failed} Failed\n";
echo "==========================================\n";

exit($failed > 0 ? 1 : 0);
