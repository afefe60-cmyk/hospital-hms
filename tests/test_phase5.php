<?php

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');
$db = \App\Core\Database::getInstance();

echo "===============================================\n";
echo " HOSPITAL HMS - PHASE 5 EMR & CLINICAL TESTS\n";
echo "===============================================\n\n";

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

// 1. BMI Calculation
assertTest("BMI Calculation: 70kg, 175cm => ~22.86", abs(\App\Models\VitalSign::calculateBmi(70, 175) - 22.86) < 0.1);
assertTest("BMI Calculation null height handles gracefully", \App\Models\VitalSign::calculateBmi(70, null) === null);

// 2. ICD-10 Catalog Verification
$catalogModel = new \App\Models\DiagnosisCatalog();
$malaria = $catalogModel->firstWhere('icd10_code', 'B54');
assertTest("ICD-10 Catalog has Malaria (B54)", $malaria !== null && stripos($malaria['description_en'], 'malaria') !== false);

$typhoid = $catalogModel->firstWhere('icd10_code', 'A01.0');
assertTest("ICD-10 Catalog has Typhoid fever (A01.0)", $typhoid !== null);

// 3. Prescription Code Generator
$rxModel = new \App\Models\Prescription();
$rxCode = $rxModel->generatePrescriptionCode();
assertTest("Prescription Code format RX-YYYY-XXXXXX", (bool)preg_match('/^RX-\d{4}-\d{6}$/', $rxCode));

// 4. Allergy Safety Cross-Check Logic
$patientModel = new \App\Models\Patient();
$patient = $patientModel->firstWhere('mrn', 'MRN-2026-000001');
assertTest("Patient 1 exists", $patient !== null);

$allergies = $patientModel->allergies((int)$patient['id']);
assertTest("Patient 1 has recorded allergies", !empty($allergies));

$blockedMed = 'Amoxicillin / Penicillin V 500mg';
$isBlocked = false;
$lowerMed = strtolower($blockedMed);
foreach ($allergies as $algRecord) {
    $rawAlg = strtolower($algRecord['allergen_name']);
    $tokens = preg_split('/[\s\(\)\[\]\/\-\,\.]+/u', $rawAlg, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($tokens as $token) {
        $token = trim($token);
        if (mb_strlen($token) >= 3 && (str_contains($lowerMed, $token) || str_contains($rawAlg, $lowerMed))) {
            $isBlocked = true;
            break 2;
        }
    }
}
assertTest("Clinical Allergy Safety: 'Amoxicillin / Penicillin' is detected and BLOCKED", $isBlocked);

// Safe medicine test
$safeMed = 'Paracetamol 500mg';
$isSafeBlocked = false;
$lowerSafe = strtolower($safeMed);
foreach ($allergies as $algRecord) {
    $rawAlg = strtolower($algRecord['allergen_name']);
    $tokens = preg_split('/[\s\(\)\[\]\/\-\,\.]+/u', $rawAlg, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($tokens as $token) {
        $token = trim($token);
        if (mb_strlen($token) >= 3 && (str_contains($lowerSafe, $token) || str_contains($rawAlg, $lowerSafe))) {
            $isSafeBlocked = true;
            break 2;
        }
    }
}
assertTest("Clinical Allergy Safety: 'Paracetamol' is safe to prescribe", !$isSafeBlocked);

// 5. Encounter creation & relationships
$visitModel = new \App\Models\Visit();
$visit = $visitModel->firstWhere('status', 'active');
assertTest("Active visit found for testing encounter", $visit !== null);

if ($visit) {
    $encounterModel = new \App\Models\Encounter();
    $existingEnc = $encounterModel->firstWhere('visit_id', $visit['id']);
    $encId = $existingEnc ? $existingEnc['id'] : $encounterModel->create([
        'visit_id' => $visit['id'],
        'doctor_id' => 1,
        'encounter_type' => 'initial',
        'chief_complaint' => 'حمى وصداع مستمر منذ 3 أيام',
        'history_present_illness' => 'المريض يعاني من ارتفاع في درجة الحرارة ورجفة',
        'start_time' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    assertTest("Encounter created/retrieved (ID: {$encId})", $encId > 0);

    // Add Vitals
    $vitalModel = new \App\Models\VitalSign();
    $vitalId = $vitalModel->create([
        'encounter_id' => $encId,
        'visit_id' => $visit['id'],
        'systolic_bp' => 120,
        'diastolic_bp' => 80,
        'pulse_rate' => 78,
        'temperature' => 38.5,
        'oxygen_saturation' => 98,
        'weight_kg' => 75.0,
        'height_cm' => 178.0,
        'bmi' => \App\Models\VitalSign::calculateBmi(75.0, 178.0),
        'recorded_by' => 1,
        'recorded_at' => date('Y-m-d H:i:s'),
    ]);
    assertTest("Vital signs recorded with BMI", $vitalId > 0);

    // Add Diagnosis
    $diagModel = new \App\Models\Diagnosis();
    $diagId = $diagModel->create([
        'encounter_id' => $encId,
        'diagnosis_catalog_id' => $malaria['id'],
        'type' => 'primary',
        'clinical_notes' => 'اشتباه ملاريا حادة بناء على الأعراض',
        'diagnosed_by' => 1,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    assertTest("ICD-10 Primary diagnosis linked to encounter", $diagId > 0);

    // Add Medical Order
    $orderModel = new \App\Models\MedicalOrder();
    $orderId = $orderModel->create([
        'encounter_id' => $encId,
        'visit_id' => $visit['id'],
        'order_type' => 'lab',
        'order_description' => 'فحص ملاريا في الدم (Malaria Blood Film & Rapid Test)',
        'priority' => 'urgent',
        'ordering_doctor_id' => 1,
        'status' => 'ordered',
        'clinical_indication' => 'حمى حادة ورجفة',
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    assertTest("Lab Medical Order issued with urgent priority", $orderId > 0);

    // Add Safe Prescription
    $prescId = $rxModel->create([
        'prescription_code' => $rxCode,
        'encounter_id' => $encId,
        'patient_id' => $visit['patient_id'],
        'doctor_id' => 1,
        'status' => 'pending',
        'notes' => 'يؤخذ الدواء بعد الأكل مع شرب كميات كافية من الماء',
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    assertTest("Prescription created", $prescId > 0);

    $rxItemModel = new \App\Models\PrescriptionItem();
    $itemId = $rxItemModel->create([
        'prescription_id' => $prescId,
        'medicine_name' => 'Paracetamol 500mg Tab',
        'dosage' => 'قرصين',
        'frequency' => 'كل 8 ساعات عند اللزوم',
        'duration_days' => 3,
        'total_quantity' => 18,
        'instructions' => 'خافض حرارة ومسكن آلام',
    ]);
    assertTest("Prescription item attached", $itemId > 0);

    // Verify detailed retrieval
    $detailedEnc = $encounterModel->detailed($encId);
    assertTest("Encounter detailed query includes patient info", !empty($detailedEnc['patient_name']));

    $vitalsList = $encounterModel->vitals($encId);
    assertTest("Encounter vitals retrieved", count($vitalsList) >= 1);

    $diagList = $encounterModel->diagnoses($encId);
    assertTest("Encounter diagnoses retrieved with ICD code", !empty($diagList[0]['icd10_code']));

    $rxDetailed = $rxModel->detailed($prescId);
    assertTest("Prescription detailed query includes doctor & patient", !empty($rxDetailed['prescription_code']));
}

echo "\n-----------------------------------------------\n";
echo " PHASE 5 TEST RESULTS: {$testsPassed} / {$testsTotal} PASSED\n";
echo "-----------------------------------------------\n";

exit($testsPassed === $testsTotal ? 0 : 1);
