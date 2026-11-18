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

// 1. Departments View
$depCtrl = new \App\Controllers\DepartmentController();
$depHtml = $depCtrl->index(new \App\Core\Request(), new \App\Core\Response());
$hasDep = str_contains($depHtml, 'الهيكل التنظيمي والأقسام') && str_contains($depHtml, 'DEP-ER');
echo "DEPARTMENTS_VIEW_TEST: " . ($hasDep ? "PASS" : "FAIL") . "\n";

// 2. Clinics View
$clinicCtrl = new \App\Controllers\ClinicController();
$clinicHtml = $clinicCtrl->index(new \App\Core\Request(), new \App\Core\Response());
$hasClinic = str_contains($clinicHtml, 'العيادات الخارجية') && str_contains($clinicHtml, 'عيادة الباطنية');
echo "CLINICS_VIEW_TEST: " . ($hasClinic ? "PASS" : "FAIL") . "\n";

// 3. Doctors View
$docCtrl = new \App\Controllers\DoctorController();
$docHtml = $docCtrl->index(new \App\Core\Request(), new \App\Core\Response());
$hasDoctor = str_contains($docHtml, 'سجل الأطباء والاستشاريين') && str_contains($docHtml, 'طارق');
echo "DOCTORS_VIEW_TEST: " . ($hasDoctor ? "PASS" : "FAIL") . "\n";

// 4. Employees View
$empCtrl = new \App\Controllers\EmployeeController();
$empHtml = $empCtrl->index(new \App\Core\Request(), new \App\Core\Response());
$hasEmp = str_contains($empHtml, 'سجل الموظفين والكوادر') && str_contains($empHtml, 'EMP-DOC-001');
echo "EMPLOYEES_VIEW_TEST: " . ($hasEmp ? "PASS" : "FAIL") . "\n";

// 5. Bed Matrix View
$bedCtrl = new \App\Controllers\BedController();
$bedHtml = $bedCtrl->index(new \App\Core\Request(), new \App\Core\Response());
$hasBed = str_contains($bedHtml, 'خريطة الأسِرّة والأجنحة التفاعلية') && str_contains($bedHtml, 'عنبر الجراحة رجال');
echo "BED_MATRIX_VIEW_TEST: " . ($hasBed ? "PASS" : "FAIL") . "\n";
