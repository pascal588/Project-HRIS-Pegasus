<?php

use Illuminate\Support\Facades\Route;
// routes/api.php
use App\Http\Controllers\Api\DivisionController;
use App\Http\Controllers\Api\EmployeeApiController;
use App\Http\Controllers\Api\RoleApiController;
use App\Http\Controllers\Api\Rolecontroller;
use App\Http\Controllers\Api\KpiController;

// api divisi
Route::apiResource('divisions', DivisionController::class);

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