<?php

// routes/api.php - Perbaikan route untuk roles
use App\Http\Controllers\Api\DivisionController;
use App\Http\Controllers\Api\EmployeeApiController;
use App\Http\Controllers\Api\RoleApiController;
use App\Http\Controllers\Api\KpiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceApiController;



Route::get('/attendances', [AttendanceApiController::class, 'index']);
Route::get('/attendances/periods', [AttendanceApiController::class, 'getPeriods']);
Route::post('/attendances/import', [AttendanceApiController::class, 'import']);
Route::get('/attendances/summary', [AttendanceApiController::class, 'getSummary']);
Route::get('/attendances/employee/{employee_id}', [AttendanceApiController::class, 'getEmployeeAttendance']);

// routes/api.php
Route::get('/divisions', [DivisionController::class, 'index']);
Route::post('/divisions', [DivisionController::class, 'store']);
Route::get('/divisions/{division}', [DivisionController::class, 'show']);
Route::put('/divisions/{division}', [DivisionController::class, 'update']);
Route::delete('/divisions/{division}', [DivisionController::class, 'destroy']);

// Tambahkan routes untuk karyawan divisi
Route::get('/divisions/{division}/employees', [DivisionController::class, 'getEmployees']);
Route::put('/divisions/{division}/head', [DivisionController::class, 'updateHead']);

Route::get('/divisions/{divisionId}/employee-count', [DivisionController::class, 'getEmployeeCount']);
Route::get('/divisions/{divisionId}/kpi-data', [DivisionController::class, 'getKpiData']); // Anda perlu membuat method ini
Route::get('/divisions/{divisionId}/employee-count', [DivisionController::class, 'getEmployeeCount']);
Route::get('/divisions/{divisionId}/gender-data', [DivisionController::class, 'getGenderData']);

//api karyawan
Route::get('/employees/kepala-divisi', [EmployeeApiController::class, 'kepalaDivisi']);

Route::apiResource('employees', EmployeeApiController::class);
Route::post('/employees/{employee}/roles', [EmployeeApiController::class, 'updateRoles']);
//api jabatan - PERBAIKAN: gunakan 'roles' bukan 'role'
Route::apiResource('roles', RoleApiController::class);

// KPI routes
Route::get('/roles-by-division/{divisionId}', [KpiController::class, 'getRolesByDivision']);
Route::get('/kpi-by-role/{roleId}', [KpiController::class, 'getKpisByRole']);
Route::post('/save-kpi', [KpiController::class, 'saveKpi']);
Route::delete('/division/{divisionId}/kpi/{kpiId}', [KpiController::class, 'deleteKpi']);
Route::delete('/kpi-question/{questionId}', [KpiController::class, 'deleteQuestion']);
