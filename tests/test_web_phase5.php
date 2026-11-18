<?php

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');

// Authenticate as admin
$admin = (new \App\Models\User())->findByUsername('admin');
\App\Core\Session::start();
\App\Core\Session::set('user_id', (int)$admin['id']);
\App\Core\Session::set('username', $admin['username']);
\App\Core\Session::set('full_name', $admin['full_name']);
\App\Core\Session::set('user_type', $admin['user_type']);

$emrCtrl = new \App\Controllers\EmrController();
$req = new \App\Core\Request();
$res = new \App\Core\Response();

// 1. Doctor EMR Dashboard View
$dashHtml = $emrCtrl->dashboard($req, $res);
$hasDash = str_contains($dashHtml, 'محطة الطبيب السريرية') && str_contains($dashHtml, 'طابور المرضى');
echo "EMR_DASHBOARD_VIEW_TEST: " . ($hasDash ? "PASS" : "FAIL") . "\n";

// 2. Clinical Encounter Screen View
$enc = (new \App\Models\Encounter())->firstWhere('id', 1);
$encId = $enc ? (string)$enc['id'] : '1';
$encHtml = $emrCtrl->encounter($req, $res, $encId);
$hasEnc = str_contains($encHtml, 'جلسة كشف') && str_contains($encHtml, 'العلامات الحيوية') && str_contains($encHtml, 'ICD-10');
echo "EMR_ENCOUNTER_VIEW_TEST: " . ($hasEnc ? "PASS" : "FAIL") . "\n";

// 3. Allergy Alert displayed in Encounter Screen
$hasAllergyAlert = str_contains($encHtml, 'تحذير طبي حرج') && str_contains($encHtml, 'Penicillin');
echo "EMR_ENCOUNTER_ALLERGY_ALERT_TEST: " . ($hasAllergyAlert ? "PASS" : "FAIL") . "\n";

// 4. Printable Prescription View
$rx = (new \App\Models\Prescription())->firstWhere('id', 1);
$rxId = $rx ? (string)$rx['id'] : '1';
$printHtml = $emrCtrl->printPrescription($req, $res, $rxId);
$hasPrint = str_contains($printHtml, 'مستشفى الأمل التخصصي') && str_contains($printHtml, '℞') && str_contains($printHtml, 'Paracetamol');
echo "EMR_PRINT_PRESCRIPTION_TEST: " . ($hasPrint ? "PASS" : "FAIL") . "\n";
