<?php

/** @var \App\Core\Router $router */

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;

// Public & Home Routes
$router->get('/', 'HomeController@index');
$router->get('/lang', 'HomeController@switchLanguage');

// Healthcheck Route
$router->get('/health', function (\App\Core\Request $request, \App\Core\Response $response) {
    try {
        $db = \App\Core\Database::getInstance();
        $dbStatus = true;
    } catch (\Throwable $e) {
        $dbStatus = false;
    }

    return $response->json([
        'status' => 'healthy',
        'database' => $dbStatus,
        'php' => PHP_VERSION,
        'timestamp' => date('c'),
    ]);
});

// Authentication Routes (Guests only)
$router->get('/login', 'AuthController@showLogin', [GuestMiddleware::class]);
$router->post('/login', 'AuthController@login', [GuestMiddleware::class, CsrfMiddleware::class]);

// Authenticated Routes
$router->post('/logout', 'AuthController@logout', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/profile', 'AuthController@profile', [AuthMiddleware::class]);
$router->post('/profile/password', 'AuthController@updatePassword', [AuthMiddleware::class, CsrfMiddleware::class]);

// Reception & Front Desk (Phase 4)
$router->get('/reception', 'ReceptionController@index', [AuthMiddleware::class]);

// Patients Management (Phase 4)
$router->get('/patients', 'PatientController@index', [AuthMiddleware::class]);
$router->get('/patients/create', 'PatientController@create', [AuthMiddleware::class]);
$router->post('/patients', 'PatientController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/patients/{id}', 'PatientController@show', [AuthMiddleware::class]);
$router->post('/patients/{id}/allergies', 'PatientController@addAllergy', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/patients/{id}/conditions', 'PatientController@addCondition', [AuthMiddleware::class, CsrfMiddleware::class]);

// Appointments & Scheduling (Phase 4)
$router->get('/appointments', 'AppointmentController@index', [AuthMiddleware::class]);
$router->get('/appointments/create', 'AppointmentController@create', [AuthMiddleware::class]);
$router->post('/appointments', 'AppointmentController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/appointments/{id}/check-in', 'AppointmentController@checkIn', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/appointments/{id}/cancel', 'AppointmentController@cancel', [AuthMiddleware::class, CsrfMiddleware::class]);

// Queue Management (Phase 4)
$router->get('/queue/display/{id}', 'QueueController@display');
$router->post('/queue/{id}/call', 'QueueController@callNext', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/queue/{id}/complete', 'QueueController@complete', [AuthMiddleware::class, CsrfMiddleware::class]);

// EMR & Clinical Encounters (Phase 5)
$router->get('/emr', 'EmrController@dashboard', [AuthMiddleware::class]);
$router->post('/emr/visit/{id}/start', 'EmrController@startEncounter', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/emr/encounter/{id}', 'EmrController@encounter', [AuthMiddleware::class]);
$router->post('/emr/encounter/{id}/vitals', 'EmrController@saveVitals', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/emr/encounter/{id}/notes', 'EmrController@saveNotes', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/emr/encounter/{id}/diagnosis', 'EmrController@addDiagnosis', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/emr/encounter/{id}/prescription', 'EmrController@addPrescription', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/emr/encounter/{id}/order', 'EmrController@addOrder', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/emr/encounter/{id}/complete', 'EmrController@completeEncounter', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/emr/prescription/{id}/print', 'EmrController@printPrescription', [AuthMiddleware::class]);

// Emergency & Triage (Phase 6)
$router->get('/emergency', 'EmergencyController@index', [AuthMiddleware::class]);
$router->get('/emergency/create', 'EmergencyController@create', [AuthMiddleware::class]);
$router->post('/emergency', 'EmergencyController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/emergency/{id}', 'EmergencyController@show', [AuthMiddleware::class]);
$router->post('/emergency/{id}/status', 'EmergencyController@updateStatus', [AuthMiddleware::class, CsrfMiddleware::class]);

// Inpatient Admissions & Bed Allocation IPD (Phase 6)
$router->get('/admissions', 'AdmissionController@index', [AuthMiddleware::class]);
$router->get('/admissions/create', 'AdmissionController@create', [AuthMiddleware::class]);
$router->post('/admissions', 'AdmissionController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/admissions/{id}', 'AdmissionController@show', [AuthMiddleware::class]);
$router->post('/admissions/{id}/transfer-bed', 'AdmissionController@transferBed', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/admissions/{id}/discharge', 'AdmissionController@discharge', [AuthMiddleware::class, CsrfMiddleware::class]);

// Nursing Station & MAR (Phase 6)
$router->get('/nursing', 'NursingController@index', [AuthMiddleware::class]);
$router->post('/nursing/{id}/note', 'NursingController@addNote', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/nursing/{id}/care-plan', 'NursingController@addCarePlan', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/nursing/mar/{id}', 'NursingController@recordMar', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/nursing/{id}/mar-dose', 'NursingController@addMarDose', [AuthMiddleware::class, CsrfMiddleware::class]);

// Laboratory Management LIS (Phase 7)
$router->get('/lab', 'LabController@index', [AuthMiddleware::class]);
$router->get('/lab/create', 'LabController@create', [AuthMiddleware::class]);
$router->post('/lab', 'LabController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/lab/{id}', 'LabController@show', [AuthMiddleware::class]);
$router->post('/lab/{id}/collect-sample', 'LabController@collectSample', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/lab/{id}/results', 'LabController@saveResults', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/lab/{id}/approve', 'LabController@approveResults', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/lab/{id}/print', 'LabController@printReport', [AuthMiddleware::class]);

// Radiology Management RIS (Phase 7)
$router->get('/radiology', 'RadiologyController@index', [AuthMiddleware::class]);
$router->get('/radiology/create', 'RadiologyController@create', [AuthMiddleware::class]);
$router->post('/radiology', 'RadiologyController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/radiology/{id}', 'RadiologyController@show', [AuthMiddleware::class]);
$router->post('/radiology/{id}/status', 'RadiologyController@updateStatus', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/radiology/{id}/report', 'RadiologyController@saveReport', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/radiology/{id}/print', 'RadiologyController@printReport', [AuthMiddleware::class]);

// Pharmacy Management & Dispensing (Phase 8)
$router->get('/pharmacy', 'PharmacyController@index', [AuthMiddleware::class]);
$router->get('/pharmacy/dispense', 'PharmacyController@dispense', [AuthMiddleware::class]);
$router->get('/pharmacy/dispense/{id}', 'PharmacyController@dispense', [AuthMiddleware::class]);
$router->post('/pharmacy/dispense', 'PharmacyController@storeDispense', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/pharmacy/receipt/{id}', 'PharmacyController@receipt', [AuthMiddleware::class]);
$router->get('/pharmacy/receipt/{id}/print', 'PharmacyController@printReceipt', [AuthMiddleware::class]);

// Inventory & Warehouses Management (Phase 8)
$router->get('/inventory', 'InventoryController@index', [AuthMiddleware::class]);
$router->get('/inventory/medicines', 'InventoryController@medicines', [AuthMiddleware::class]);
$router->get('/inventory/medicines/create', 'InventoryController@createMedicine', [AuthMiddleware::class]);
$router->post('/inventory/medicines', 'InventoryController@storeMedicine', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/inventory/batches', 'InventoryController@batches', [AuthMiddleware::class]);
$router->get('/inventory/batches/create', 'InventoryController@createBatch', [AuthMiddleware::class]);
$router->post('/inventory/batches', 'InventoryController@storeBatch', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/inventory/suppliers', 'InventoryController@suppliers', [AuthMiddleware::class]);
$router->post('/inventory/suppliers', 'InventoryController@storeSupplier', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/inventory/purchase-orders', 'InventoryController@purchaseOrders', [AuthMiddleware::class]);

// Surgery & Operating Rooms OR (Phase 9)
$router->get('/surgery', 'SurgeryController@index', [AuthMiddleware::class]);
$router->get('/surgery/create', 'SurgeryController@create', [AuthMiddleware::class]);
$router->post('/surgery/store', 'SurgeryController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/surgery/show', 'SurgeryController@show', [AuthMiddleware::class]);
$router->get('/surgery/{id}', 'SurgeryController@show', [AuthMiddleware::class]);
$router->get('/surgery/print', 'SurgeryController@printReport', [AuthMiddleware::class]);
$router->get('/surgery/{id}/print', 'SurgeryController@printReport', [AuthMiddleware::class]);
$router->post('/surgery/update-status', 'SurgeryController@updateStatus', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/surgery/save-anesthesia', 'SurgeryController@saveAnesthesia', [AuthMiddleware::class, CsrfMiddleware::class]);

// Intensive Care Unit ICU (Phase 9)
$router->get('/icu', 'IcuController@index', [AuthMiddleware::class]);
$router->get('/icu/create', 'IcuController@create', [AuthMiddleware::class]);
$router->post('/icu/store', 'IcuController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/icu/show', 'IcuController@show', [AuthMiddleware::class]);
$router->get('/icu/{id}', 'IcuController@show', [AuthMiddleware::class]);
$router->get('/icu/print', 'IcuController@printChart', [AuthMiddleware::class]);
$router->get('/icu/{id}/print', 'IcuController@printChart', [AuthMiddleware::class]);
$router->post('/icu/record-observation', 'IcuController@recordObservation', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/icu/update-status', 'IcuController@updateStatus', [AuthMiddleware::class, CsrfMiddleware::class]);

// Finance, Medical Billing & Payments (Phase 10)
$router->get('/billing', 'BillingController@index', [AuthMiddleware::class]);
$router->get('/billing/create', 'BillingController@create', [AuthMiddleware::class]);
$router->post('/billing/store', 'BillingController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/billing/services', 'BillingController@services', [AuthMiddleware::class]);
$router->post('/billing/services', 'BillingController@storeService', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/billing/show', 'BillingController@show', [AuthMiddleware::class]);
$router->get('/billing/{id}', 'BillingController@show', [AuthMiddleware::class]);
$router->post('/billing/payment', 'BillingController@addPayment', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/billing/{id}/print', 'BillingController@printInvoice', [AuthMiddleware::class]);
$router->get('/billing/receipt/{id}/print', 'BillingController@printReceipt', [AuthMiddleware::class]);

// Cashbox Shifts & Drawer Reconciliation (Phase 10)
$router->get('/cashboxes', 'CashboxController@index', [AuthMiddleware::class]);
$router->post('/cashboxes/open', 'CashboxController@openSession', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/cashboxes/close', 'CashboxController@closeSession', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/cashboxes/sessions/{id}', 'CashboxController@showSession', [AuthMiddleware::class]);
$router->get('/cashboxes/sessions/{id}/print', 'CashboxController@printSession', [AuthMiddleware::class]);

// Health Insurance & Claims Management (Phase 11)
$router->get('/insurance', 'InsuranceController@index', [AuthMiddleware::class]);
$router->get('/insurance/companies', 'InsuranceController@companies', [AuthMiddleware::class]);
$router->post('/insurance/companies', 'InsuranceController@storeCompany', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/insurance/policies', 'InsuranceController@policies', [AuthMiddleware::class]);
$router->post('/insurance/policies', 'InsuranceController@storePolicy', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/insurance/claims/create', 'InsuranceController@createClaim', [AuthMiddleware::class]);
$router->post('/insurance/claims', 'InsuranceController@storeClaim', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/insurance/claims/{id}', 'InsuranceController@showClaim', [AuthMiddleware::class]);
$router->post('/insurance/claims/status', 'InsuranceController@updateClaimStatus', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/insurance/claims/{id}/print', 'InsuranceController@printClaim', [AuthMiddleware::class]);
$router->get('/insurance/batch/print', 'InsuranceController@printBatch', [AuthMiddleware::class]);

// Hospital Structure Routes (Phase 3)
$router->get('/departments', 'DepartmentController@index', [AuthMiddleware::class]);
$router->post('/departments', 'DepartmentController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/departments/{id}/toggle-status', 'DepartmentController@toggleStatus', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/clinics', 'ClinicController@index', [AuthMiddleware::class]);
$router->post('/clinics', 'ClinicController@store', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/doctors', 'DoctorController@index', [AuthMiddleware::class]);

$router->get('/employees', 'EmployeeController@index', [AuthMiddleware::class]);
$router->post('/employees', 'EmployeeController@store', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/beds', 'BedController@index', [AuthMiddleware::class]);
$router->post('/beds/{id}/status', 'BedController@updateStatus', [AuthMiddleware::class, CsrfMiddleware::class]);

// Users Management Routes
$router->get('/users', 'UserController@index', [AuthMiddleware::class]);
$router->get('/users/create', 'UserController@create', [AuthMiddleware::class]);
$router->post('/users', 'UserController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/users/{id}/edit', 'UserController@edit', [AuthMiddleware::class]);
$router->post('/users/{id}', 'UserController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/users/{id}/toggle-status', 'UserController@toggleStatus', [AuthMiddleware::class, CsrfMiddleware::class]);

// Roles & Permissions RBAC Routes
$router->get('/roles', 'RoleController@index', [AuthMiddleware::class]);
$router->get('/roles/{id}/permissions', 'RoleController@permissions', [AuthMiddleware::class]);
$router->post('/roles/{id}/permissions', 'RoleController@updatePermissions', [AuthMiddleware::class, CsrfMiddleware::class]);

// Audit Logs
$router->get('/audit-logs', 'AuditLogController@index', [AuthMiddleware::class]);

// Human Resources (HR), Rosters & Payroll (Phase 12)
$router->get('/hr', 'HrController@index', [AuthMiddleware::class]);
$router->get('/hr/roster', 'HrController@roster', [AuthMiddleware::class]);
$router->post('/hr/roster', 'HrController@storeRoster', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/hr/roster/update-status', 'HrController@updateRosterStatus', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/hr/leaves', 'HrController@leaves', [AuthMiddleware::class]);
$router->post('/hr/leaves', 'HrController@storeLeave', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/hr/leaves/update-status', 'HrController@updateLeaveStatus', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/hr/payroll', 'HrController@payroll', [AuthMiddleware::class]);
$router->post('/hr/payroll/generate', 'HrController@generatePayroll', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/hr/payroll/print-sheet', 'HrController@printPayrollSheet', [AuthMiddleware::class]);
$router->get('/hr/payroll/{id}', 'HrController@showPayroll', [AuthMiddleware::class]);
$router->post('/hr/payroll/pay', 'HrController@paySalary', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/hr/payroll/print/{id}', 'HrController@printPayslip', [AuthMiddleware::class]);
