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

// 1. Reception Hub View
$recCtrl = new \App\Controllers\ReceptionController();
$recHtml = $recCtrl->index(new \App\Core\Request(), new \App\Core\Response());
$hasRec = str_contains($recHtml, 'مكتب الاستقبال') && str_contains($recHtml, 'البحث السريع');
echo "RECEPTION_VIEW_TEST: " . ($hasRec ? "PASS" : "FAIL") . "\n";

// 2. Patients Directory View
$patCtrl = new \App\Controllers\PatientController();
$patHtml = $patCtrl->index(new \App\Core\Request(), new \App\Core\Response());
$hasPat = str_contains($patHtml, 'سجل المرضى والملف الطبي') && str_contains($patHtml, 'MRN-2026-000001');
echo "PATIENTS_INDEX_VIEW_TEST: " . ($hasPat ? "PASS" : "FAIL") . "\n";

// 3. Patient 360-Degree Dossier View with Red Allergy Alert
$p1 = (new \App\Models\Patient())->firstWhere('mrn', 'MRN-2026-000001');
$patShowHtml = $patCtrl->show(new \App\Core\Request(), new \App\Core\Response(), (string)$p1['id']);
$hasShow = str_contains($patShowHtml, 'ملف المريض') && str_contains($patShowHtml, 'Penicillin') && str_contains($patShowHtml, 'تحذير طبي حرج');
echo "PATIENT_SHOW_DOSSIER_TEST: " . ($hasShow ? "PASS" : "FAIL") . "\n";

// 4. Appointments View
$appCtrl = new \App\Controllers\AppointmentController();
$appHtml = $appCtrl->index(new \App\Core\Request(), new \App\Core\Response());
$hasApp = str_contains($appHtml, 'جدول المواعيد وحجوزات العيادات');
echo "APPOINTMENTS_VIEW_TEST: " . ($hasApp ? "PASS" : "FAIL") . "\n";

// 5. Queue Public TV Display
$qCtrl = new \App\Controllers\QueueController();
$qHtml = $qCtrl->display(new \App\Core\Request(), new \App\Core\Response(), "1");
$hasQueueDisplay = str_contains($qHtml, 'الرقم الحالي قيد الكشف') && str_contains($qHtml, 'قائمة الانتظار');
echo "QUEUE_TV_DISPLAY_TEST: " . ($hasQueueDisplay ? "PASS" : "FAIL") . "\n";
