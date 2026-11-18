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
