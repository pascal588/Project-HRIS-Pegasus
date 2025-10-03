<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DivisionController;
use App\Http\Controllers\Api\EmployeeApiController;
use App\Http\Controllers\Api\RoleApiController;
use App\Http\Controllers\Api\KpiController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\PeriodController;

// ==================== ATTENDANCE ROUTES ====================
Route::prefix('attendances')->group(function () {
  Route::get('/', [AttendanceApiController::class, 'index']);
  Route::get('/periods', [AttendanceApiController::class, 'getPeriods']);
  Route::post('/import', [AttendanceApiController::class, 'import']);
  Route::get('/summary', [AttendanceApiController::class, 'getSummary']);
  Route::get('/{id}', [AttendanceApiController::class, 'show']);
  Route::get('/employee/{employee_id}', [AttendanceApiController::class, 'getEmployeeAttendance']);
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
  Route::get('/by-division-except-head/{divisionId}', [EmployeeApiController::class, 'getEmployeesByDivisionExceptHead']);

  // ✅ TAMBAHKAN INI DI SINI:
  Route::get('/jumlahkaryawan-by-month', [EmployeeApiController::class, 'getEmployeeTotalByMonth']);

  // ⚠️ ROUTE PARAMETER HARUS DITULIS TERAKHIR
  Route::get('/{employee}', [EmployeeApiController::class, 'show']);
  Route::put('/{employee}', [EmployeeApiController::class, 'update']);
  Route::delete('/{employee}', [EmployeeApiController::class, 'destroy']);
});

// ==================== ROLE ROUTES ====================
Route::apiResource('roles', RoleApiController::class);

// ==================== KPI ROUTES (DIPERBAIKI) ====================
Route::prefix('kpis')->group(function () {
  // KPI Templates & Management
  Route::get('/', [KpiController::class, 'getAllKpis']);
  Route::post('/', [KpiController::class, 'storeKpi']);
  Route::get('/division-performance', [KpiController::class, 'getDivisionKpiPerformance']);
  Route::get('/templates', [KpiController::class, 'getKpiTemplates']);
  Route::post('/copy-to-period/{periodId}', [KpiController::class, 'copyTemplatesToPeriod']);
  Route::get('/period/{periodId}', [KpiController::class, 'getKpisByPeriod']);
  Route::get('/all-employee-scores', [KpiController::class, 'getAllEmployeeKpis']);
Route::get('/low-performing-employees-all', [KpiController::class, 'getLowPerformingEmployeesAllDivisions']);

  // KPI by Division
  Route::get('/division/{divisionId}', [KpiController::class, 'listKpiByDivision']);
  Route::get('/division/{divisionId}/period/{periodeId}', [KpiController::class, 'getKpisByPeriod']);
  Route::get('/division/{divisionId}/total-weight', [KpiController::class, 'getTotalWeightByDivision']);

  // Global KPI
  Route::get('/global', [KpiController::class, 'listGlobalKpi']);

  // KPI Scoring & Evaluation
  // Route::post('/score', [KpiController::class, 'storeEmployeeScore']);
  Route::get('/all-employee-scores', [KpiController::class, 'getAllEmployeeKpis']);
  Route::post('/submit-answers', [KpiController::class, 'storeEmployeeScore']);
  Route::get('/attendance-summary/{employeeId}/{periodeId}', [KpiController::class, 'getAttendanceSummary']);
  Route::post('/calculate/{employeeId}/{periodeId}', [KpiController::class, 'calculateFinalScore']);
  Route::get('/scores/{employeeId}/{periodeId}', [KpiController::class, 'getScoreByAspekUtama']);
  Route::get('/employee/{employeeId}/period/{periodId}', [KpiController::class, 'getEmployeeKpiForPeriod']);
  Route::get('/employee/{employeeId}/period/{periodId}/status', [KpiController::class, 'getEmployeeKpiStatus']);
  // KPI Detail Routes
  Route::get('/employee/{employeeId}/detail', [KpiController::class, 'getEmployeeKpiDetail']);
  Route::get('/employee/{employeeId}/detail/{periodId}', [KpiController::class, 'getEmployeeKpiDetail']);

  // KPI CRUD Operations
  Route::put('/{id}', [KpiController::class, 'updateKpi']);
  Route::delete('/{id}', [KpiController::class, 'deleteKpi']);
  Route::delete('/point/{id}', [KpiController::class, 'deleteKpiPoint']);
  Route::delete('/question/{id}', [KpiController::class, 'deleteKpiQuestion']);

  // Specific KPI Types
  Route::delete('/global/{id}', [KpiController::class, 'deleteGlobalKpi']);
  Route::put('/global/{id}', [KpiController::class, 'updateGlobalKpi']);

  // ✅ PERBAIKAN: Route publishing yang benar
  Route::get('/available-periods-publishing', [KpiController::class, 'getAvailablePeriodsForPublishing']);
  Route::post('/publish-to-period', [KpiController::class, 'publishToPeriod']);

  Route::get('/attendance-calculation/{employeeId}/{periodeId}', [KpiController::class, 'getAttendanceCalculationData']);

  // Tambahkan di routes/api.php

    Route::get('/division/{divisionId}/unrated-employees', [KpiController::class, 'getUnratedEmployees']);
    Route::get('/division/{divisionId}/low-performing-employees', [KpiController::class, 'getLowPerformingEmployees']);
    Route::get('/division/{divisionId}/stats', [KpiController::class, 'getDivisionKpiStats']);

    Route::get('/published-periods', [KpiController::class, 'getPublishedPeriods']);
Route::get('/period/{periodId}/scores', [KpiController::class, 'getEmployeeScoresByPeriod']);
Route::get('/available-years', [KpiController::class, 'getAvailableYears']);
Route::get('/year/{year}/scores', [KpiController::class, 'getScoresByYear']);
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
