<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DivisionController;
use App\Http\Controllers\Api\EmployeeApiController;
use App\Http\Controllers\Api\RoleApiController;
use App\Http\Controllers\Api\KpiController;
use Illuminate\Support\Facades\Route;

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

  // ==== EMPLOYEES ====
  Route::apiResource('employees', EmployeeApiController::class);

  // Update role karyawan
  Route::post('/employees/{employee}/roles', [EmployeeApiController::class, 'updateRoles']);

  // Ambil semua kepala divisi 
  Route::get('/employees/kepala-divisi', [EmployeeApiController::class, 'kepalaDivisi']);

  // ==== ROLES ====
  Route::apiResource('roles', RoleApiController::class);

  // ==== KPI ====
  Route::prefix('kpi')->group(function () {
    Route::get('/', [KpiController::class, 'getAllKpis']); 
    Route::get('/roles-by-division/{division}', [KpiController::class, 'getRolesByDivision']);
    Route::get('/by-role/{role}', [KpiController::class, 'getKpisByRole']);
    Route::post('/save', [KpiController::class, 'saveKpi']);
    Route::delete('/division/{division}/kpi/{kpi}', [KpiController::class, 'deleteKpi']);
    Route::delete('/question/{question}', [KpiController::class, 'deleteQuestion']);
  });

