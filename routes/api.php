<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DivisionController;
use App\Http\Controllers\Api\EmployeeApiController;
use App\Http\Controllers\Api\RoleApiController;
// use App\Http\Controllers\Api\KpiController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\PeriodController;
use App\Http\Controllers\Api\KpiTemplateController;
use App\Http\Controllers\Api\KpiPeriodController;
use App\Http\Controllers\Api\KpiEvaluationController;

// ==================== ATTENDANCE ROUTES ====================
Route::prefix('attendances')->group(function () {
  Route::get('/', [AttendanceApiController::class, 'index']);
  Route::get('/periods', [AttendanceApiController::class, 'getPeriods']);
  Route::post('/import', [AttendanceApiController::class, 'import']);
  Route::get('/summary', [AttendanceApiController::class, 'getSummary']);
  Route::get('/{id}', [AttendanceApiController::class, 'show']);
  Route::get('/employee/{employee_id}', [AttendanceApiController::class, 'getEmployeeAttendance']);
  Route::get('/employee/{employeeId}/period/{periodId}', [AttendanceController::class, 'getEmployeeAttendanceByPeriod']);

  Route::get('/employees-with-attendance/{periodId}', [AttendanceApiController::class, 'getEmployeesWithAttendance']);
});

// ==================== DIVISION ROUTES ====================
Route::prefix('divisions')->group(function () {
  Route::get('/', [DivisionController::class, 'index']);
  Route::post('/', [DivisionController::class, 'store']);
  Route::get('/{division}', [DivisionController::class, 'show']);
  Route::put('/{division}', [DivisionController::class, 'update']);
  Route::delete('/{division}', [DivisionController::class, 'destroy']);

  // Division specific routes
  Route::get('/{division}/employees', [DivisionController::class, 'getEmployees']);
  Route::put('/{division}/head', [DivisionController::class, 'updateHead']);
  Route::get('/{division}/employee-count', [DivisionController::class, 'getEmployeeCount']);
  Route::get('/{division}/kpi-data', [DivisionController::class, 'getKpiData']);
  Route::get('/{division}/gender-data', [DivisionController::class, 'getGenderData']);
});

// ==================== EMPLOYEE ROUTES ====================
Route::prefix('employees')->group(function () {
  Route::get('/', [EmployeeApiController::class, 'index']);
  Route::post('/', [EmployeeApiController::class, 'store']);

  // ⚠️ PASTIKAN ROUTE KHUSUS DITULIS SEBELUM ROUTE PARAMETER
  Route::get('/Kepala Divisi', [EmployeeApiController::class, 'kepalaDivisi']);
  Route::post('/{employee}/roles', [EmployeeApiController::class, 'updateRoles']);
  Route::get('/by-division/{divisionId}', [EmployeeApiController::class, 'getEmployeesByDivision']);
  // Route::get('/by-division-except-head/{divisionId}', [EmployeeApiController::class, 'getEmployeesByDivisionExceptHead']);

  // ✅ TAMBAHKAN INI DI SINI:
  Route::get('/jumlahkaryawan-by-month', [EmployeeApiController::class, 'getEmployeeTotalByMonth']);

  // ⚠️ ROUTE PARAMETER HARUS DITULIS TERAKHIR
  Route::get('/{employee}', [EmployeeApiController::class, 'show']);
  Route::put('/{employee}', [EmployeeApiController::class, 'update']);
  Route::delete('/{employee}', [EmployeeApiController::class, 'destroy']);
});

// ==================== ROLE ROUTES ====================
Route::apiResource('roles', RoleApiController::class);

// ==================== KPI ROUTES (DIPERBAIKI CONTROLLER SAJA) ====================
Route::prefix('kpis')->group(function () {
  // KPI Templates & Management
  Route::get('/', [KpiTemplateController::class, 'getAllKpis']);
  Route::post('/', [KpiTemplateController::class, 'storeKpi']);
  Route::get('/division-performance', [KpiEvaluationController::class, 'getDivisionKpiPerformance']);
  Route::get('/templates', [KpiTemplateController::class, 'getKpiTemplates']);
  Route::post('/copy-to-period/{periodId}', [KpiPeriodController::class, 'copyTemplatesToPeriod']);
  Route::get('/period/{periodId}', [KpiPeriodController::class, 'getKpisByPeriod']);
  Route::get('/all-employee-scores', [KpiEvaluationController::class, 'getAllEmployeeKpis']);
  Route::get('/low-performing-employees-all', [KpiEvaluationController::class, 'getLowPerformingEmployeesAllDivisions']);

  // KPI by Division
  Route::get('/division/{divisionId}', [KpiPeriodController::class, 'listKpiByDivision']);
  Route::get('/division/{divisionId}/period/{periodeId}', [KpiPeriodController::class, 'getKpisByPeriod']);
  Route::get('/division/{divisionId}/total-weight', [KpiTemplateController::class, 'getTotalWeightByDivision']);

  // Global KPI
  Route::get('/global', [KpiTemplateController::class, 'listGlobalKpi']);

  // KPI Scoring & Evaluation
  Route::post('/submit-answers', [KpiEvaluationController::class, 'storeEmployeeScore']);
  Route::get('/attendance-summary/{employeeId}/{periodeId}', [KpiEvaluationController::class, 'getAttendanceSummary']);
  Route::post('/calculate/{employeeId}/{periodeId}', [KpiEvaluationController::class, 'calculateFinalScore']);
  Route::get('/scores/{employeeId}/{periodeId}', [KpiEvaluationController::class, 'getScoreByAspekUtama']);
  Route::get('/employee/{employeeId}/period/{periodId}', [KpiEvaluationController::class, 'getEmployeeKpiForPeriod']);
  Route::get('/employee/{employeeId}/period/{periodId}/status', [KpiEvaluationController::class, 'getEmployeeKpiStatus']);
  
  // KPI Detail Routes
  Route::get('/employee/{employeeId}/detail', [KpiEvaluationController::class, 'getEmployeeKpiDetail']);
  Route::get('/employee/{employeeId}/detail/{periodId}', [KpiEvaluationController::class, 'getEmployeeKpiDetail']);

  // KPI CRUD Operations
  Route::put('/{id}', [KpiTemplateController::class, 'updateKpi']);
  Route::delete('/{id}', [KpiTemplateController::class, 'deleteKpi']);
  Route::delete('/point/{id}', [KpiTemplateController::class, 'deleteKpiPoint']);
  Route::delete('/question/{id}', [KpiTemplateController::class, 'deleteKpiQuestion']);

  // Specific KPI Types
  Route::delete('/global/{id}', [KpiTemplateController::class, 'deleteKpi']);
  Route::put('/global/{id}', [KpiTemplateController::class, 'updateKpi']);

  // Route publishing
  Route::get('/available-periods-publishing', [KpiPeriodController::class, 'getAvailablePeriodsForPublishing']);
  Route::post('/publish-to-period', [KpiPeriodController::class, 'publishToPeriod']);

  Route::get('/attendance-calculation/{employeeId}/{periodeId}', [KpiEvaluationController::class, 'getAttendanceCalculationData']);

  // Division Reports
  Route::get('/division/{divisionId}/unrated-employees', [KpiEvaluationController::class, 'getUnratedEmployees']);
  Route::get('/division/{divisionId}/low-performing-employees', [KpiEvaluationController::class, 'getLowPerformingEmployees']);
  Route::get('/division/{divisionId}/stats', [KpiEvaluationController::class, 'getDivisionKpiStats']);

  // Period Reports
  Route::get('/published-periods', [KpiPeriodController::class, 'getPublishedPeriods']);
  Route::get('/period/{periodId}/scores', [KpiEvaluationController::class, 'getEmployeeScoresByPeriod']);
  Route::get('/available-years', [KpiEvaluationController::class, 'getAvailableYears']);
  Route::get('/year/{year}/scores', [KpiEvaluationController::class, 'getScoresByYear']);
      Route::get('/active/with-attendance', [PeriodController::class, 'getActivePeriodsWithAttendance']);

      Route::get('/kpi-evaluation/employees/non-head', [KpiEvaluationController::class, 'getNonHeadEmployeesKpis']);
Route::get('/kpi-evaluation/employees/non-head/scores/{periodId?}', [KpiEvaluationController::class, 'getNonHeadEmployeeScoresByPeriod']);
Route::get('/kpi-evaluation/employees/non-head/unrated', [KpiEvaluationController::class, 'getUnratedNonHeadEmployees']);
Route::get('/kpi-evaluation/employees/non-head/unrated/{divisionId}', [KpiEvaluationController::class, 'getUnratedNonHeadEmployees']);

    // Di api.php - PASTIKAN INI ADA
    Route::get('/export-monthly/{employeeId}/{year?}', [KpiEvaluationController::class, 'exportMonthlyKpi']);
    Route::get('/test-export/{employeeId}/{year?}', [KpiEvaluationController::class, 'exportMonthlyKpi']);
    Route::get('/test-export/{employeeId}/{year?}', [KpiEvaluationController::class, 'testExport']);
});

// ==================== PERIOD ROUTES ====================
Route::prefix('periods')->group(function () {
  // CRUD Operations
  Route::get('/', [PeriodController::class, 'index']);
  Route::get('/performance-across-periods', [PeriodController::class, 'getPerformanceAcrossPeriods']);
  Route::get('/{id}', [PeriodController::class, 'show']);
  Route::post('/', [PeriodController::class, 'store']);
  Route::put('/{id}', [PeriodController::class, 'update']);
  Route::delete('/{id}', [PeriodController::class, 'destroy']);

  // Period Management
  Route::post('/{id}/mark-attendance-uploaded', [PeriodController::class, 'markAttendanceUploaded']);
  Route::post('/{id}/lock', [PeriodController::class, 'lockPeriod']);
  Route::post('/{id}/unlock', [PeriodController::class, 'unlockPeriod']);
  Route::post('/{id}/activate', [PeriodController::class, 'setActive']);

  // Period Status & Reports
  Route::get('/{id}/evaluation-status', [PeriodController::class, 'getEvaluationStatus']);
  Route::get('/{id}/attendance-summary', [PeriodController::class, 'getAttendanceSummary']);
  Route::get('/{id}/kpi-summary', [PeriodController::class, 'getKpiSummary']);
  Route::get('/rekap/bulanan', [PeriodController::class, 'getMonthlyReport']);
  Route::get('/rekap/tahunan', [PeriodController::class, 'getYearlyReport']);

  // Auto-creation
  Route::post('/auto-create-from-attendance', [PeriodController::class, 'autoCreateFromAttendance']);
  Route::get('/status/check', [PeriodController::class, 'checkPeriodStatuses']);
});


// ==================== COMPATIBILITY ROUTES ====================
// Untuk kompatibilitas dengan frontend lama
Route::get('/kpi-by-division/{divisionId}', [KpiController::class, 'listKpiByDivision']);
Route::get('/kpi-global', [KpiController::class, 'listGlobalKpi']);

// Test routes for attendance data
Route::get('/kpi/test-attendance/{employeeId}/{periodeId}', [KpiController::class, 'testAttendanceData']);


Route::prefix('report')->group(function () {
    // KPI Monthly Data & Export
    Route::get('/kpi/monthly-data/{employeeId}', [App\Http\Controllers\HrController::class, 'getMonthlyKpiData']);
    Route::get('/kpi/monthly-export/{employeeId}', [App\Http\Controllers\HrController::class, 'exportMonthlyKpi']);
    
    // Detail Absensi
    Route::get('/detail-absensi/{employee_id}', [App\Http\Controllers\HrController::class, 'detailAbsensi']);
});

