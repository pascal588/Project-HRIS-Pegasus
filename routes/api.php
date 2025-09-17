<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DivisionController;
use App\Http\Controllers\Api\EmployeeApiController;
use App\Http\Controllers\Api\RoleApiController;
use App\Http\Controllers\Api\KpiController;
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

// Statistik divisi
Route::get('/divisions/{division}/employee-count', [DivisionController::class, 'getEmployeeCount']);
Route::get('/divisions/{division}/kpi-data', [DivisionController::class, 'getKpiData']);
Route::get('/divisions/{division}/gender-data', [DivisionController::class, 'getGenderData']);


// Ambil semua kepala divisi 
Route::get('/employees/Kepala Divisi', [EmployeeApiController::class, 'kepalaDivisi']);

// ==== EMPLOYEES ====
Route::apiResource('employees', EmployeeApiController::class);

// Untuk kepala divisi: ambil karyawan di divisinya
Route::get('/api/employees-by-division/{divisionId}', [KpiController::class, 'getEmployeesByDivision']);

// Update role karyawan
Route::post('/employees/{employee}/roles', [EmployeeApiController::class, 'updateRoles']);

// ==== ROLES ====
Route::apiResource('roles', RoleApiController::class);

// ==== KPI ====
Route::prefix('kpi')->group(function () {
  Route::get('/', [KpiController::class, 'getAllKpis']);
  Route::post('/save', [KpiController::class, 'storeKpi']);
  Route::put('/{kpiId}', [KpiController::class, 'updateKpi']);
  Route::delete('/{kpiId}', [KpiController::class, 'deleteKpi']);

  // Nilai karyawan
  Route::post('/score', [KpiController::class, 'storeEmployeeScore']);
  Route::post('/calculate/{employeeId}/{tahun}/{bulan}', [KpiController::class, 'calculateFinalScore']);
  Route::get('/scores/{employeeId}/{tahun}/{bulan}', [KpiController::class, 'getScoreByAspekUtama']);

  // KPI per divisi
  Route::get('/division/{divisionId}/{tahun}/{bulan}', [KpiController::class, 'getDivisionKpi']);
});

// === Kompatibilitas frontend lama ===
Route::get('/kpi-global', [KpiController::class, 'getAllKpis']);
Route::get('/kpi-by-division/{division}', [KpiController::class, 'listKpiByDivision']);

// KPI Global
Route::get('/kpi-global', [KpiController::class, 'listGlobalKpi']);
Route::delete('/kpi-global/{id}', [KpiController::class, 'deleteGlobalKpi']);
Route::put('/kpi-global/{id}', [KpiController::class, 'updateGlobalKpi']);

// KPI Divisi
Route::get('/kpi-by-division/{divisionId}', [KpiController::class, 'listKpiByDivision']);
Route::delete('/division/{divisionId}/kpi/{kpiId}', [KpiController::class, 'deleteDivisionKpi']);
Route::put('/division/{divisionId}/kpi/{kpiId}', [KpiController::class, 'updateDivisionKpi']);

// Simpan KPI baru (global atau division)
Route::post('/kpi/save', [KpiController::class, 'storeKpi']);
