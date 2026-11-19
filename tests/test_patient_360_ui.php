<?php

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');
\App\Core\Session::start();

use App\Core\Request;
use App\Core\Response;
use App\Controllers\PatientController;
use App\Models\Patient;
use App\Models\User;

echo "===================================================================\n";
echo " HOSPITAL HMS - PATIENT 360 & MEDICAL ID CARD UNIT TESTS\n";
echo "===================================================================\n\n";

$passCount = 0;
$totalTests = 0;

function assertTest(string $desc, bool $condition): void {
    global $passCount, $totalTests;
    $totalTests++;
    if ($condition) {
        $passCount++;
        echo "  [PASS] {$desc}\n";
    } else {
        echo "  [FAIL] {$desc}\n";
    }
}

// Login as admin
$userModel = new User();
$adminUser = $userModel->findByUsername('admin');
if ($adminUser) {
    \App\Core\Session::set('user_id', (int)$adminUser['id']);
    \App\Core\Session::set('username', $adminUser['username']);
    \App\Core\Session::set('full_name', $adminUser['full_name']);
    \App\Core\Session::set('user_type', $adminUser['user_type']);
}

// 1. Get first patient in DB
$patientModel = new Patient();
$patients = $patientModel->all('id', 'ASC');
$firstPatient = $patients[0] ?? null;
assertTest('Patient record exists in database for testing', !empty($firstPatient));

$patientId = (string)$firstPatient['id'];
$patientCtrl = new PatientController();

// 2. Test Patient 360 Show View
$req = new Request();
$res = new Response();
$htmlShow = $patientCtrl->show($req, $res, $patientId);

assertTest('Patient 360 show renders string content', is_string($htmlShow) && strlen($htmlShow) > 1000);
assertTest('Patient 360 view contains MRN', str_contains($htmlShow, $firstPatient['mrn']));
assertTest('Patient 360 view contains action buttons (حجز موعد، طوارئ ER، تنويم IPD)', str_contains($htmlShow, 'حجز موعد') && str_contains($htmlShow, 'طوارئ ER'));
assertTest('Patient 360 view contains print card link', str_contains($htmlShow, 'print-card'));
assertTest('Patient 360 view contains tabs (المختبرات والأشعة، التنويم والعمليات، الفواتير)', str_contains($htmlShow, 'المختبرات والأشعة') && str_contains($htmlShow, 'التنويم والعمليات') && str_contains($htmlShow, 'الفواتير والمالية'));

// 3. Test Printable Patient ID Card View
$htmlCard = $patientCtrl->printCard($req, $res, $patientId);
assertTest('Printable Patient ID card renders standalone HTML', is_string($htmlCard) && str_contains($htmlCard, '<!DOCTYPE html>'));
assertTest('Patient ID card contains Barcode representation', str_contains($htmlCard, 'barcode-text') && str_contains($htmlCard, $firstPatient['mrn']));
assertTest('Patient ID card contains Blood Group badge', str_contains($htmlCard, 'blood-badge'));
$hospName = \App\Models\SystemSetting::get('hospital_name_ar', 'مستشفى الخرطوم');
$bankakNum = \App\Models\SystemSetting::get('bankak_account_number', '1820499');
assertTest('Patient ID card contains Khartoum General Hospital identity & Bankak info', 
    str_contains($htmlCard, $hospName) || str_contains($htmlCard, $bankakNum) || str_contains($htmlCard, 'بنكك'));

// 4. Test Web Route via HTTP
$ch = curl_init('http://127.0.0.1:8080/health');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$health = curl_exec($ch);
curl_close($ch);
assertTest('Built-in server is responding', !empty($health));

echo "\n===================================================================\n";
echo " TEST RESULTS: {$passCount} / {$totalTests} PASSED\n";
echo "===================================================================\n";

if ($passCount === $totalTests) {
    exit(0);
} else {
    exit(1);
}
