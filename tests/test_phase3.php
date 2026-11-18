<?php

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');

echo "==========================================\n";
echo " HOSPITAL HMS - PHASE 3 VERIFICATION TESTS\n";
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
assertTest("Departments table exists", in_array('departments', $tables));
assertTest("Clinics table exists", in_array('clinics', $tables));
assertTest("Specialties table exists", in_array('specialties', $tables));
assertTest("Employees table exists", in_array('employees', $tables));
assertTest("Doctors table exists", in_array('doctors', $tables));
assertTest("Wards table exists", in_array('wards', $tables));
assertTest("Rooms table exists", in_array('rooms', $tables));
assertTest("Beds table exists", in_array('beds', $tables));

// 2. Department Model Test
$depModel = new \App\Models\Department();
$deps = $depModel->all();
assertTest("Departments populated (> 5)", count($deps) >= 5, "Found: " . count($deps));

$erDep = $depModel->firstWhere('code', 'DEP-ER');
assertTest("Emergency Department (DEP-ER) exists", $erDep !== null && $erDep['code'] === 'DEP-ER');

// 3. Clinic Model Test
$clinicModel = new \App\Models\Clinic();
$clinics = $clinicModel->allWithDepartment();
assertTest("Clinics with departments loaded", count($clinics) >= 2, "Found: " . count($clinics));

// 4. Doctor Model Test
$docModel = new \App\Models\Doctor();
$doctors = $docModel->allDetailed();
assertTest("Doctors loaded with specialties & fees", count($doctors) >= 2, "Found: " . count($doctors));

if (!empty($doctors)) {
    $firstDoc = $doctors[0];
    assertTest("Doctor has Sudanese Medical License", !empty($firstDoc['medical_license_number']));
    assertTest("Doctor consultation fee is positive in SDG", (float)$firstDoc['consultation_fee'] > 0);
}

// 5. Bed Matrix Test
$bedModel = new \App\Models\Bed();
$matrix = $bedModel->matrix();
assertTest("Bed Matrix structure populated", !empty($matrix));

$counts = $bedModel->countsByStatus();
assertTest("Bed Counts Total > 0", $counts['total'] > 0, "Found total: " . $counts['total']);
assertTest("Bed Counts has available beds", isset($counts['available']));

// 6. Bed Status Transition Test
$firstBed = $db->fetchOne("SELECT id, status FROM `beds` LIMIT 1");
if ($firstBed) {
    $targetStatus = $firstBed['status'] === 'available' ? 'occupied' : 'available';
    $updated = $bedModel->updateStatus((int)$firstBed['id'], $targetStatus);
    assertTest("Bed Status Transition successful", $updated === true);

    $checkBed = $bedModel->find($firstBed['id']);
    assertTest("Bed Status updated in database", $checkBed['status'] === $targetStatus);

    // Revert back
    $bedModel->updateStatus((int)$firstBed['id'], $firstBed['status']);
}

echo "\n------------------------------------------\n";
echo "Tests Completed: {$passed} Passed, {$failed} Failed\n";
echo "==========================================\n";

exit($failed > 0 ? 1 : 0);
