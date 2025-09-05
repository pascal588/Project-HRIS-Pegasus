<?php

use Illuminate\Support\Facades\Route;
// routes/api.php
use App\Http\Controllers\Api\DivisionController;
use App\Http\Controllers\Api\EmployeeApiController;
use App\Http\Controllers\Api\RoleApiController;
use App\Http\Controllers\Api\Rolecontroller;
use App\Http\Controllers\Api\KpiController;

// routes/api.php
Route::get('/divisions', [DivisionController::class, 'index']);
Route::post('/divisions', [DivisionController::class, 'store']);
Route::get('/divisions/{division}', [DivisionController::class, 'show']);
Route::put('/divisions/{division}', [DivisionController::class, 'update']);
Route::delete('/divisions/{division}', [DivisionController::class, 'destroy']);

// Tambahkan routes untuk karyawan divisi
Route::get('/divisions/{division}/employees', [DivisionController::class, 'getEmployees']);
Route::put('/divisions/{division}/head', [DivisionController::class, 'updateHead']);

//api karyawan
Route::apiResource('employees', EmployeeApiController::class);

//api jabatan
route::apiResource('role', RoleApiController::class );

// KPI routes
Route::get('/roles-by-division/{divisionId}', [KpiController::class, 'getRolesByDivision']);
Route::get('/kpi-by-role/{roleId}', [KpiController::class, 'getKpisByRole']);
Route::post('/save-kpi', [KpiController::class, 'saveKpi']);
Route::delete('/division/{divisionId}/kpi/{kpiId}', [KpiController::class, 'deleteKpi']);
Route::delete('/kpi-question/{questionId}', [KpiController::class, 'deleteQuestion']);