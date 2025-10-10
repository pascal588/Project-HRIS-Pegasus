<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kpi;
use App\Models\KpiPoint;
use App\Models\KpiQuestion;
use App\Models\KpiQuestionHasEmployee;
use App\Models\KpiPointsHasEmployee;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Period;
use App\Models\Division;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use App\Exports\MonthlyKpiExport;

class KpiEvaluationController extends Controller
{
    public function getEmployeeKpiForPeriod($employeeId, $periodId)
{
        if (!$periodId) {
        $activePeriod = Period::where('attendance_uploaded', true)
            ->where('status', 'active')
            ->orderBy('tanggal_mulai', 'desc')
            ->first();
            
        if (!$activePeriod) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada periode aktif dengan absensi'
            ], 404);
        }
        $periodId = $activePeriod->id_periode;
    }
    
    Log::info("=== getEmployeeKpiForPeriod START ===", [
        'employee_id' => $employeeId,
        'period_id' => $periodId
    ]);

    try {
        // Dapatkan data karyawan dan divisinya
        $employee = Employee::with(['roles.division'])->findOrFail($employeeId);

        // Ambil divisi karyawan (ambil divisi pertama)
        $divisionId = null;
        if ($employee->roles && count($employee->roles) > 0) {
            $divisionId = $employee->roles[0]->division_id ?? null;
        }

        Log::info("Employee data:", [
            'employee_id' => $employeeId,
            'employee_name' => $employee->nama,
            'division_id' => $divisionId,
            'roles_count' => $employee->roles->count(),
            'roles' => $employee->roles->pluck('id_jabatan')
        ]);

        // ✅ AMBIL SEMUA KPI DI PERIODE INI (tanpa syarat published)
        $kpis = Kpi::where('periode_id', $periodId)
            ->where(function ($query) use ($divisionId) {
                // Pertama coba ambil KPI divisi spesifik
                if ($divisionId) {
                    $query->whereHas('divisions', function ($q) use ($divisionId) {
                        $q->where('divisions.id_divisi', $divisionId);
                    });
                }

                // Jika tidak ada KPI divisi, ambil KPI global sebagai fallback
                if (!$query->getQuery()->wheres) {
                    $query->orWhere('is_global', true);
                }
            })
            ->with(['points.questions'])
            ->get();

        Log::info("KPI Query Results:", [
            'total_kpis_found' => $kpis->count(),
            'division_id_used' => $divisionId,
            'kpi_details' => $kpis->map(function ($kpi) {
                return [
                    'id' => $kpi->id_kpi,
                    'nama' => $kpi->nama,
                    'is_global' => $kpi->is_global,
                    'division_count' => $kpi->divisions->count(),
                    'points_count' => $kpi->points->count()
                ];
            })
        ]);

        $data = [];

        foreach ($kpis as $kpi) {
            $pointsData = [];

            // Dapatkan kpis_has_employees ID untuk mengambil nilai point
            $kpisHasEmployeeId = DB::table('kpis_has_employees')
                ->where('kpis_id_kpi', $kpi->id_kpi)
                ->where('employees_id_karyawan', $employeeId)
                ->where('periode_id', $periodId)
                ->value('id');

            Log::info("KPI Has Employee ID:", [
                'kpi_id' => $kpi->id_kpi,
                'kpi_name' => $kpi->nama,
                'kpis_has_employee_id' => $kpisHasEmployeeId
            ]);

            foreach ($kpi->points as $point) {
                $questionsData = [];
                $isAbsensi = stripos($point->nama, 'absensi') !== false || 
                             stripos($point->nama, 'kehadiran') !== false;

                Log::info("Processing Point:", [
                    'point_id' => $point->id_point,
                    'point_name' => $point->nama,
                    'is_absensi' => $isAbsensi
                ]);

                // ⚠️ PERBAIKAN: Untuk absensi, ambil nilai dari kolom `nilai_absensi`
                $pointScore = 0;
                if ($isAbsensi && $kpisHasEmployeeId) {
                    $pointRecord = DB::table('kpi_points_has_employee')
                        ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                        ->where('kpi_point_id', $point->id_point)
                        ->first();

                    // ⚠️ PERBAIKAN: Ambil dari nilai_absensi
                    $pointScore = $pointRecord->nilai_absensi ?? 0;
                    
                    Log::info("Absensi Point Score:", [
                        'point_id' => $point->id_point,
                        'point_score' => $pointScore,
                        'record_exists' => !is_null($pointRecord)
                    ]);
                }

                foreach ($point->questions as $q) {
                    $answer = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                        ->where('kpi_question_id_question', $q->id_question)
                        ->where('periode_id', $periodId)
                        ->first();

                    $questionsData[] = [
                        'id_question' => $q->id_question,
                        'pertanyaan' => $q->pertanyaan,
                        'answer' => $answer ? (int)$answer->nilai : null,
                        'answered_at' => $answer ? $answer->updated_at : null
                    ];

                    Log::info("Question Data:", [
                        'question_id' => $q->id_question,
                        'has_answer' => !is_null($answer),
                        'answer_value' => $answer ? $answer->nilai : null
                    ]);
                }

                $pointsData[] = [
                    'id_point' => $point->id_point,
                    'nama' => $point->nama,
                    'bobot' => (float)$point->bobot,
                    'is_absensi' => $isAbsensi,
                    'point_score' => (float)$pointScore,
                    'questions' => $questionsData
                ];
            }

            $data[] = [
                'id_kpi' => $kpi->id_kpi,
                'aspek' => $kpi->nama,
                'nama' => $kpi->nama,
                'bobot' => (float)$kpi->bobot,
                'is_global' => $kpi->is_global,
                'points' => $pointsData
            ];
        }

        Log::info("Final KPI Data for Employee:", [
            'employee_id' => $employeeId,
            'total_kpis_returned' => count($data),
            'kpis' => array_map(function ($item) {
                return [
                    'id' => $item['id_kpi'],
                    'nama' => $item['nama'],
                    'is_global' => $item['is_global'],
                    'points_count' => count($item['points'])
                ];
            }, $data)
        ]);

        return response()->json([
            'success' => true,
            'data' => $data,
            'employee_division' => $divisionId,
            'debug_info' => [
                'division_id' => $divisionId,
                'total_kpis' => count($data),
                'kpi_list' => array_column($data, 'nama'),
                'period_id' => $periodId
            ]
        ]);

    } catch (\Exception $e) {
        Log::error("Error in getEmployeeKpiForPeriod: " . $e->getMessage());
        Log::error("Stack trace: " . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Error getting employee KPI: ' . $e->getMessage()
        ], 500);
    }
}

    public function getEmployeeKpiStatus($employeeId, $periodId)
    {
        try {
            $employee = Employee::with(['roles.division'])->findOrFail($employeeId);

            // Ambil KPI untuk employee di periode tertentu
            $kpis = Kpi::where('periode_id', $periodId)
                ->where(function ($query) use ($employee) {
                    $divisionId = null;
                    if ($employee->roles && count($employee->roles) > 0) {
                        $divisionId = $employee->roles[0]->division_id ?? null;
                    }

                    $query->where('is_global', true);

                    if ($divisionId) {
                        $query->orWhereHas('divisions', function ($q) use ($divisionId) {
                            $q->where('divisions.id_divisi', $divisionId);
                        });
                    }
                })
                ->with(['points.questions'])
                ->get();

            $hasAnswers = false;
            $totalQuestions = 0;
            $answeredQuestions = 0;
            $totalScore = 0;

            foreach ($kpis as $kpi) {
                foreach ($kpi->points as $point) {
                    foreach ($point->questions as $question) {
                        $totalQuestions++;
                        $answer = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                            ->where('kpi_question_id_question', $question->id_question)
                            ->where('periode_id', $periodId)
                            ->first();

                        if ($answer && $answer->nilai !== null) {
                            $answeredQuestions++;
                            $hasAnswers = true;
                        }
                    }
                }
            }

            // Jika ada jawaban, hitung total score
            if ($hasAnswers) {
                $kpiScores = DB::table('kpis_has_employees')
                    ->where('employees_id_karyawan', $employeeId)
                    ->where('periode_id', $periodId)
                    ->get();

                $totalScore = $kpiScores->sum('nilai_akhir');
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'has_answers' => $hasAnswers,
                    'answered_questions' => $answeredQuestions,
                    'total_questions' => $totalQuestions,
                    'completion_percentage' => $totalQuestions > 0 ? ($answeredQuestions / $totalQuestions) * 100 : 0,
                    'total_score' => $totalScore,
                    'status' => $hasAnswers ? 'Sudah Dinilai' : 'Belum Dinilai'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking KPI status: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getScoreByAspekUtama($employeeId, $periodeId)
    {
        $kpis = Kpi::where('periode_id', $periodeId)->with(['points.questions'])->get();
        $scores = [];
        foreach ($kpis as $kpi) {
            $aspekScore = 0;
            foreach ($kpi->points as $point) {
                $pointScore = 0;
                foreach ($point->questions as $q) {
                    $pointScore += KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                        ->where('kpi_question_id_question', $q->id_question)
                        ->where('periode_id', $periodeId)
                        ->first()?->nilai ?? 0;
                }
                $aspekScore += $pointScore;
            }
            $scores[] = ['aspek' => $kpi->nama, 'score' => $aspekScore];
        }
        return response()->json(['success' => true, 'data' => $scores]);
    }
    
    // Attendance-related methods
    public function getAttendanceSummary($employeeId, $periodeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $period = Period::findOrFail($periodeId);

        $attendances = Attendance::where('employee_id', $employeeId)
            ->where('periode_id', $periodeId)
            ->get()
            ->groupBy('status');

        $summary = [
            'total_days' => $attendances->count(),
            'hadir' => $attendances->where('status', 'Present at workday (PW)')->count(),
            'izin' => $attendances->where('status', 'Permission (I)')->count(),
            'sakit' => $attendances->where('status', 'Sick (S)')->count(),
            'mangkir' => $attendances->where('status', 'Absent (A)')->count(),
            'terlambat' => $attendances->sum('late'),
            'jumlah_terlambat' => $attendances->where('late', '>', 0)->count(),
            'early_leave' => $attendances->sum('early_leave')
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => $employee->nama,
                'period' => $period->nama,
                'attendance_summary' => $summary
            ]
        ]);
    }

    public function getAttendanceConfig($pointId = null)
    {
        try {
            $config = [
                'hadir_multiplier' => 3,
                'sakit_multiplier' => 0,
                'izin_multiplier' => 0,
                'mangkir_multiplier' => -3,
                'terlambat_multiplier' => -2,
                'workday_multiplier' => 2
            ];

            return response()->json([
                'success' => true,
                'data' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading attendance config: ' . $e->getMessage()
            ], 500);
        }
    }



/**
 * Ambil konfigurasi multiplier dari point absensi
 */
private function getPointAttendanceConfig($point)
{
    try {
        // Cek jika ada konfigurasi di kolom tambahan
        // Anda perlu menambah kolom di tabel kpi_points untuk menyimpan config JSON
        if ($point->attendance_config) {
            return json_decode($point->attendance_config, true);
        }

        // Jika tidak ada, coba ambil dari kolom description atau buat mapping
        // Sementara return default berdasarkan nama point
        $pointName = strtolower($point->nama);
        
        if (strpos($pointName, 'absensi') !== false || strpos($pointName, 'kehadiran') !== false) {
            return [
                'hadir_multiplier' => 3,
                'sakit_multiplier' => 0,
                'izin_multiplier' => 0,
                'mangkir_multiplier' => -3,
                'terlambat_multiplier' => -2,
                'workday_multiplier' => 2
            ];
        }

        return null;
    } catch (\Exception $e) {
        Log::error('Error getting point attendance config: ' . $e->getMessage());
        return null;
    }
}

public function getAllEmployeeKpis(Request $request)
{
    try {
        Log::info("=== getAllEmployeeKpis - ONLY UNRATED in ACTIVE PERIOD ===");

        $activePeriods = Period::where('kpi_published', true)
            ->where('attendance_uploaded', true)
            ->where('status', 'active')
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        if ($activePeriods->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Tidak ada periode AKTIF dengan absensi dan KPI yang dipublish'
            ]);
        }

        $allEmployeeData = [];

        foreach ($activePeriods as $period) {
            Log::info("Processing ACTIVE period: {$period->nama} (ID: {$period->id_periode})");

            // Ambil semua Kepala Divisi
            $kepalaDivisiEmployees = DB::table('employees')
                ->join('roles_has_employees', 'employees.id_karyawan', '=', 'roles_has_employees.employee_id')
                ->join('roles', 'roles_has_employees.role_id', '=', 'roles.id_jabatan')
                ->select(
                    'employees.id_karyawan',
                    'employees.nama',
                    'employees.status',
                    'employees.foto',
                    'roles.nama_jabatan as position',
                    'roles.division_id'
                )
                ->where('employees.status', 'Aktif')
                ->where('roles.nama_jabatan', 'like', '%Kepala Divisi%')
                ->get();

            foreach ($kepalaDivisiEmployees as $emp) {
                // Cek apakah memiliki absensi di periode ini
                $hasAttendance = DB::table('attendances')
                    ->where('employee_id', $emp->id_karyawan)
                    ->where('periode_id', $period->id_periode)
                    ->exists();

                if (!$hasAttendance) {
                    continue;
                }

                // ⚠️ HITUNG TOTAL SCORE LANGSUNG: SUM(nilai_akhir) × 10
                $totalScore = DB::table('kpis_has_employees')
                    ->where('employees_id_karyawan', $emp->id_karyawan)
                    ->where('periode_id', $period->id_periode)
                    ->sum('nilai_akhir');

                $finalScore = $totalScore * 10; // ⚠️ INI PERKALIAN ×10

                // ✅ CUMA TAMPILIN YANG BELUM DINILAI (score = 0)
                if ($finalScore > 0) {
                    continue;
                }

                // Ambil detail divisi
                $division = '-';
                $divisionId = $emp->division_id;
                
                if ($divisionId) {
                    $divisionData = DB::table('divisions')
                        ->where('id_divisi', $divisionId)
                        ->first();
                    $division = $divisionData ? $divisionData->nama_divisi : '-';
                }

                $periodMonth = date('F', strtotime($period->tanggal_mulai));
                $periodYear = date('Y', strtotime($period->tanggal_mulai));

                // ✅ PERBAIKAN PATH FOTO
                $photoUrl = $emp->foto ? asset('storage/' . $emp->foto) : asset('assets/images/profile_av.png');

                $employeeData = [
                    'id_karyawan' => $emp->id_karyawan,
                    'nama' => $emp->nama,
                    'status' => $emp->status,
                    'score' => $finalScore, // ⚠️ SUDAH ×10
                    'period' => $period->nama,
                    'period_month' => $periodMonth,
                    'period_month_number' => date('n', strtotime($period->tanggal_mulai)),
                    'period_year' => $periodYear,
                    'photo' => $photoUrl,
                    'division' => $division,
                    'division_id' => $divisionId,
                    'position' => $emp->position,
                    'period_id' => $period->id_periode
                ];

                $allEmployeeData[] = $employeeData;
                Log::info("✅ Added UNRATED employee:", [
                    'nama' => $emp->nama,
                    'period' => $period->nama,
                    'score' => $finalScore
                ]);
            }
        }

        Log::info("Final UNRATED employees:", [
            'total_unrated' => count($allEmployeeData),
            'active_periods' => $activePeriods->count()
        ]);

        return response()->json([
            'success' => true,
            'data' => $allEmployeeData,
            'debug_info' => [
                'total_active_periods' => $activePeriods->count(),
                'total_unrated_employees' => count($allEmployeeData),
                'note' => 'Score = SUM(nilai_akhir) × 10, hanya menampilkan yang score = 0'
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to fetch UNRATED KPI data: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch KPI data: ' . $e->getMessage()
        ], 500);
    }
}
    
public function getEmployeeScoresByPeriod($periodId)
{
    try {
        $period = Period::findOrFail($periodId);

        // ⚠️ HITUNG LANGSUNG: SUM(nilai_akhir) × 10
        $employeeScores = DB::table('kpis_has_employees')
            ->where('periode_id', $periodId)
            ->join('employees', 'kpis_has_employees.employees_id_karyawan', '=', 'employees.id_karyawan')
            ->select(
                'employees.id_karyawan',
                'employees.nama',
                'employees.status',
                'employees.foto',
                DB::raw('SUM(kpis_has_employees.nilai_akhir) * 10 as total_score') // ⚠️ ×10 DI SINI
            )
            ->where('employees.status', 'Aktif')
            ->groupBy('employees.id_karyawan', 'employees.nama', 'employees.status', 'employees.foto')
            ->orderBy('total_score', 'desc')
            ->get();

        $formattedData = [];

        foreach ($employeeScores as $emp) {
            $employeeDetails = Employee::with(['roles.division'])->find($emp->id_karyawan);
            $division = '-';
            $position = '-';
            
            if ($employeeDetails && $employeeDetails->roles->count() > 0) {
                $division = $employeeDetails->roles[0]->division->nama_divisi ?? '-';
                $position = $employeeDetails->roles[0]->nama_jabatan ?? '-';
            }

            $periodMonth = date('F', strtotime($period->tanggal_mulai));
            $periodYear = date('Y', strtotime($period->tanggal_selesai));

            $formattedData[] = [
                'id_karyawan' => $emp->id_karyawan,
                'nama' => $emp->nama,
                'status' => $emp->status,
                'score' => floatval($emp->total_score), // ⚠️ INI SUDAH ×10
                'period' => $period->nama,
                'period_month' => $periodMonth,
                'period_month_number' => date('n', strtotime($period->tanggal_mulai)),
                'period_year' => $periodYear,
                'photo' => $emp->foto ? asset('storage/' . $emp->foto) : asset('assets/images/profile_av.png'),
                'division' => $division,
                'position' => $position,
                'period_id' => $periodId
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $formattedData,
            'period_info' => [
                'id' => $period->id_periode,
                'nama' => $period->nama,
                'tanggal_mulai' => $period->tanggal_mulai,
                'period_month' => date('F', strtotime($period->tanggal_mulai))
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to fetch KPI data for period: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch KPI data: ' . $e->getMessage()
        ], 500);
    }
}
    
public function getEmployeeKpiDetail($employeeId, $periodId = null)
{
    try {
        Log::info("getEmployeeKpiDetail called:", [
            'employee_id' => $employeeId,
            'period_id' => $periodId
        ]);

        $employee = Employee::with(['roles.division'])->findOrFail($employeeId);
        
        if (!$periodId) {
            $activePeriod = Period::where('status', 'active')
                ->where('kpi_published', true)
                ->first();
            
            if (!$activePeriod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada periode aktif'
                ], 404);
            }
            $periodId = $activePeriod->id_periode;
        }

        $period = Period::findOrFail($periodId);

        // ⚠️ PERBAIKAN: HITUNG TOTAL SCORE DENGAN RUMUS BARU
        $totalScore = $this->calculateTotalScoreWithNewFormula($employeeId, $periodId);

        // ⚠️ PERBAIKAN: Ambil detail KPI dengan rumus baru
        $kpiDetails = $this->getKpiDetailsWithNewFormula($employeeId, $periodId);

        // Hitung ranking
        $allEmployeeScores = DB::table('kpis_has_employees')
            ->where('periode_id', $periodId)
            ->select('employees_id_karyawan', DB::raw('SUM(nilai_akhir) * 10 as total_score')) // ⚠️ ×10
            ->groupBy('employees_id_karyawan')
            ->orderBy('total_score', 'desc')
            ->get();

        $ranking = 1;
        foreach ($allEmployeeScores as $empScore) {
            if ($empScore->employees_id_karyawan == $employeeId) break;
            $ranking++;
        }

        $totalEmployees = $allEmployeeScores->count();

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => [
                    'id_karyawan' => $employee->id_karyawan,
                    'nama' => $employee->nama,
                    'division' => $employee->roles->first()->division->nama_divisi ?? '-',
                    'position' => $employee->roles->first()->nama_jabatan ?? '-'
                ],
                'period' => $period,
                'kpi_summary' => [
                    'total_score' => round($totalScore, 2),
                    'average_score' => round($totalScore, 2),
                    'average_contribution' => round($totalScore, 2),
                    'performance_status' => $this->getStatusByContribution($totalScore),
                    'ranking' => $ranking,
                    'total_employees' => $totalEmployees
                ],
                'kpi_details' => $kpiDetails
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error getting employee KPI detail: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error getting KPI detail: ' . $e->getMessage()
        ], 500);
    }
}

// ⚠️ METHOD BARU: Hitung total score dengan rumus baru
private function calculateTotalScoreWithNewFormula($employeeId, $periodId)
{
    try {
        $employee = Employee::with(['roles.division'])->find($employeeId);
        $divisionId = $employee->roles->first()->division_id ?? null;

        $kpis = Kpi::where('periode_id', $periodId)
            ->where(function ($query) use ($divisionId) {
                $query->where('is_global', true);
                if ($divisionId) {
                    $query->orWhereHas('divisions', function ($q) use ($divisionId) {
                        $q->where('divisions.id_divisi', $divisionId);
                    });
                }
            })
            ->with(['points.questions'])
            ->get();

        $totalAllKpis = 0;

        foreach ($kpis as $kpi) {
            $totalAspekScore = 0;

            foreach ($kpi->points as $point) {
                $pointScore = 0;
                $isAbsensi = stripos($point->nama, 'absensi') !== false;

                if ($isAbsensi) {
                    // RUMUS ABSENSI: (nilai_absensi × bobot) / 100
                    $kpisHasEmployeeId = DB::table('kpis_has_employees')
                        ->where('kpis_id_kpi', $kpi->id_kpi)
                        ->where('employees_id_karyawan', $employeeId)
                        ->where('periode_id', $periodId)
                        ->value('id');

                    if ($kpisHasEmployeeId) {
                        $pointRecord = DB::table('kpi_points_has_employee')
                            ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                            ->where('kpi_point_id', $point->id_point)
                            ->first();

                        if ($pointRecord) {
                            $pointScore = ($pointRecord->nilai_absensi * floatval($point->bobot)) / 100;
                        }
                    }
                } else {
                    // RUMUS NORMAL: (rata-rata × 2.5) × (bobot / 100)
                    $pointTotal = 0;
                    $answeredQuestions = 0;

                    foreach ($point->questions as $q) {
                        $score = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                            ->where('kpi_question_id_question', $q->id_question)
                            ->where('periode_id', $periodId)
                            ->first();

                        if ($score && $score->nilai !== null) {
                            $pointTotal += $score->nilai;
                            $answeredQuestions++;
                        }
                    }

                    if ($answeredQuestions > 0) {
                        $avgQuestionScore = $pointTotal / $answeredQuestions;
                        $pointScore = ($avgQuestionScore * 2.5) * (floatval($point->bobot) / 100);
                    }
                }

                $totalAspekScore += $pointScore;
            }

            $totalAllKpis += $totalAspekScore;
        }

        // ⚠️ RUMUS FINAL: Total semua aspek × 10
        return $totalAllKpis * 10;

    } catch (\Exception $e) {
        Log::error('Error calculating total score: ' . $e->getMessage());
        return 0;
    }
}

// ⚠️ METHOD BARU: Get KPI details dengan rumus baru
private function getKpiDetailsWithNewFormula($employeeId, $periodId)
{
    $kpiData = [];
    
    $employee = Employee::with(['roles.division'])->find($employeeId);
    $divisionId = $employee->roles->first()->division_id ?? null;

    $kpis = Kpi::where('periode_id', $periodId)
        ->where(function ($query) use ($divisionId) {
            $query->where('is_global', true);
            if ($divisionId) {
                $query->orWhereHas('divisions', function ($q) use ($divisionId) {
                    $q->where('divisions.id_divisi', $divisionId);
                });
            }
        })
        ->with(['points.questions'])
        ->get();

    foreach ($kpis as $kpi) {
        $aspekUtama = $kpi->nama;
        $totalAspekScore = 0;

        // DETAIL SUB-ASPEK
        $kpisHasEmployeeId = DB::table('kpis_has_employees')
            ->where('kpis_id_kpi', $kpi->id_kpi)
            ->where('employees_id_karyawan', $employeeId)
            ->where('periode_id', $periodId)
            ->value('id');

        foreach ($kpi->points as $point) {
            $pointScore = 0;
            $isAbsensi = stripos($point->nama, 'absensi') !== false;

            if ($isAbsensi && $kpisHasEmployeeId) {
                $pointRecord = DB::table('kpi_points_has_employee')
                    ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                    ->where('kpi_point_id', $point->id_point)
                    ->first();
                
                // RUMUS ABSENSI: (nilai_absensi × bobot) / 100
                $pointScore = ($pointRecord->nilai_absensi * floatval($point->bobot)) / 100;
                
                $kpiData[] = [
                    'aspek_kpi' => $aspekUtama,
                    'sub_aspek_name' => $point->nama,
                    'score' => $pointRecord->nilai_absensi ?? 0, // Nilai mentah
                    'bobot' => floatval($point->bobot),
                    'kontribusi' => $pointScore, // Kontribusi setelah dikali bobot
                    'is_total_aspek' => false
                ];
            } else {
                // RUMUS NORMAL: (rata-rata × 2.5) × (bobot / 100)
                $pointTotal = 0;
                $answeredQuestions = 0;

                foreach ($point->questions as $question) {
                    $answer = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                        ->where('kpi_question_id_question', $question->id_question)
                        ->where('periode_id', $periodId)
                        ->first();

                    if ($answer && $answer->nilai !== null) {
                        $pointTotal += $answer->nilai;
                        $answeredQuestions++;
                    }
                }

                $rawScore = 0;
                $pointScore = 0;

                if ($answeredQuestions > 0) {
                    $avgQuestionScore = $pointTotal / $answeredQuestions;
                    $rawScore = $avgQuestionScore * 2.5; // Konversi ke 0-10
                    $pointScore = $rawScore * (floatval($point->bobot) / 100);
                }

                $kpiData[] = [
                    'aspek_kpi' => $aspekUtama,
                    'sub_aspek_name' => $point->nama,
                    'score' => $rawScore, // Nilai mentah (0-10)
                    'bobot' => floatval($point->bobot),
                    'kontribusi' => $pointScore, // Kontribusi setelah dikali bobot
                    'is_total_aspek' => false
                ];
            }

            $totalAspekScore += $pointScore;
        }

        // TOTAL ASPEK UTAMA
        $kpiData[] = [
            'aspek_kpi' => $aspekUtama,
            'sub_aspek_name' => 'TOTAL ASPEK',
            'score' => $totalAspekScore * 10, // Nilai aspek × 10
            'bobot' => floatval($kpi->bobot),
            'kontribusi' => $totalAspekScore, // Kontribusi asli
            'is_total_aspek' => true
        ];
    }

    return $kpiData;
}



/**
 * GET unrated employees for DASHBOARD (sama dengan kpi-karyawan.blade.php)
 */
public function getUnratedEmployeesForDashboard($divisionId)
{
    try {
        \Log::info("getUnratedEmployeesForDashboard called", ['division_id' => $divisionId]);

        // ⚠️ PAKAI LOGIKA YANG SAMA dengan getAllNonHeadEmployees
        $activePeriods = Period::where('kpi_published', true)
            ->where('attendance_uploaded', true)
            ->where('status', 'active')
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        if ($activePeriods->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Tidak ada periode AKTIF dengan absensi dan KPI yang dipublish'
            ]);
        }

        $unratedEmployees = [];

        foreach ($activePeriods as $period) {
            \Log::info("Processing ACTIVE period for dashboard: {$period->nama} (ID: {$period->id_periode})");

            // ⚠️ LOGIKA SAMA PERSIS dengan getAllNonHeadEmployees
            $nonHeadEmployees = DB::table('employees')
                ->join('roles_has_employees', 'employees.id_karyawan', '=', 'roles_has_employees.employee_id')
                ->join('roles', 'roles_has_employees.role_id', '=', 'roles.id_jabatan')
                ->select(
                    'employees.id_karyawan',
                    'employees.nama',
                    'employees.status',
                    'employees.foto',
                    'employees.no_telp',
                    'roles.nama_jabatan as position',
                    'roles.division_id'
                )
                ->where('employees.status', 'Aktif')
                ->where('roles.nama_jabatan', 'not like', '%Kepala Divisi%')
                ->where('roles.division_id', $divisionId)
                ->get();

            \Log::info("Non-head employees found for dashboard: " . $nonHeadEmployees->count());

            foreach ($nonHeadEmployees as $emp) {
                // Cek absensi
                $hasAttendance = DB::table('attendances')
                    ->where('employee_id', $emp->id_karyawan)
                    ->where('periode_id', $period->id_periode)
                    ->exists();

                if (!$hasAttendance) {
                    continue;
                }

                // ⚠️ HITUNG SCORE dengan rumus yang sama
                $totalScore = DB::table('kpis_has_employees')
                    ->where('employees_id_karyawan', $emp->id_karyawan)
                    ->where('periode_id', $period->id_periode)
                    ->sum('nilai_akhir');

                $finalScore = $totalScore * 10;

                // ⚠️ FILTER: HANYA YANG SCORE = 0 (belum dinilai)
                if ($finalScore == 0) {
                    // Format data SAMA PERSIS dengan getAllNonHeadEmployees
                    $division = '-';
                    $divisionId = $emp->division_id;
                    
                    if ($divisionId) {
                        $divisionData = DB::table('divisions')
                            ->where('id_divisi', $divisionId)
                            ->first();
                        $division = $divisionData ? $divisionData->nama_divisi : '-';
                    }

                    $periodMonth = date('F', strtotime($period->tanggal_mulai));
                    $periodYear = date('Y', strtotime($period->tanggal_mulai));

                    // Format foto SAMA PERSIS
                    $fotoPath = $emp->foto;
                    if (!$fotoPath || $fotoPath === '' || $fotoPath === 'null') {
                        $fotoPath = 'assets/images/profile_av.png';
                    } else {
                        if (filter_var($fotoPath, FILTER_VALIDATE_URL)) {
                            // Already full URL
                        } elseif (strpos($fotoPath, 'storage/') === 0) {
                            $fotoPath = asset($fotoPath);
                        } elseif (strpos($fotoPath, 'profile-photos/') === 0) {
                            $fotoPath = asset('storage/' . $fotoPath);
                        } else {
                            $fotoPath = asset('storage/' . $fotoPath);
                        }
                    }

                    $unratedEmployees[] = [
                        'id_karyawan' => $emp->id_karyawan,
                        'nama' => $emp->nama,
                        'foto' => $fotoPath, // ⚠️ FORMAT SAMA
                        'division' => $division,
                        'position' => $emp->position,
                        'score' => 0,
                        'period' => $period->nama, // ⚠️ FORMAT SAMA
                        'period_month' => $periodMonth,
                        'period_month_number' => date('n', strtotime($period->tanggal_mulai)),
                        'period_year' => $periodYear,
                        'division_id' => $divisionId,
                        'period_id' => $period->id_periode,
                        'phone' => $emp->no_telp
                    ];

                    \Log::info("✅ Added UNRATED employee to dashboard:", [
                        'nama' => $emp->nama,
                        'score' => $finalScore,
                        'division' => $division
                    ]);
                }
            }
        }

        \Log::info("Final unrated employees for dashboard:", [
            'total_unrated' => count($unratedEmployees),
            'division_id' => $divisionId
        ]);

        return response()->json([
            'success' => true,
            'data' => $unratedEmployees,
            'debug_info' => [
                'total_unrated' => count($unratedEmployees),
                'division_id' => $divisionId,
                'note' => 'Data unrated employees untuk dashboard (format sama dengan kpi-karyawan)'
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in getUnratedEmployeesForDashboard: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}
    public function getLowPerformingEmployees($divisionId)
{
    try {
        \Log::info("getLowPerformingEmployees called", ['division_id' => $divisionId]);

        // ⚠️ PERBAIKAN: Gunakan periode AKTIF yang sama dengan getAllEmployeeKpis
        $activePeriods = Period::where('kpi_published', true)
            ->where('attendance_uploaded', true)
            ->where('status', 'active')
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        if ($activePeriods->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Tidak ada periode AKTIF dengan absensi dan KPI yang dipublish'
            ]);
        }

        $lowPerformers = [];

        foreach ($activePeriods as $period) {
            \Log::info("Processing ACTIVE period for low performers: {$period->nama} (ID: {$period->id_periode})");

            // ⚠️ PERBAIKAN QUERY: Hitung score dengan rumus yang sama ×10
            $periodLowPerformers = DB::table('kpis_has_employees')
                ->where('periode_id', $period->id_periode)
                ->join('employees', 'kpis_has_employees.employees_id_karyawan', '=', 'employees.id_karyawan')
                ->select(
                    'employees.id_karyawan',
                    'employees.nama',
                    'employees.foto',
                    'employees.no_telp as phone',
                    DB::raw('SUM(kpis_has_employees.nilai_akhir) * 10 as total_score') // ⚠️ ×10 DI SINI
                )
                ->where('employees.status', 'Aktif')
                ->groupBy('employees.id_karyawan', 'employees.nama', 'employees.foto', 'employees.no_telp')
                ->having('total_score', '<=', 50) // ⚠️ FILTER: score <= 50 (setelah ×10)
                ->orderBy('total_score', 'asc')
                ->get();

            // Filter berdasarkan divisi
            foreach ($periodLowPerformers as $emp) {
                $employeeDetails = Employee::with(['roles.division'])->find($emp->id_karyawan);
                
                // Cek apakah karyawan termasuk di divisi yang diminta
                $isInDivision = false;
                if ($employeeDetails && $employeeDetails->roles->count() > 0) {
                    foreach ($employeeDetails->roles as $role) {
                        if ($role->division_id == $divisionId) {
                            $isInDivision = true;
                            break;
                        }
                    }
                }

                if ($isInDivision) {
                    $lowPerformers[] = [
                        'id_karyawan' => $emp->id_karyawan,
                        'nama' => $emp->nama,
                        'foto' => $emp->foto,
                        'phone' => $emp->phone,
                        'position' => $employeeDetails->roles->first()->nama_jabatan ?? '-',
                        'score' => floatval($emp->total_score), // ⚠️ INI SUDAH ×10
                        'period_id' => $period->id_periode,
                        'period_name' => $period->nama
                    ];
                }
            }
        }

        \Log::info("Low performers found:", [
            'division_id' => $divisionId,
            'total_low_performers' => count($lowPerformers)
        ]);

        return response()->json([
            'success' => true,
            'data' => $lowPerformers,
            'period_info' => [
                'total_active_periods' => $activePeriods->count(),
                'note' => 'Score = SUM(nilai_akhir) × 10, filter score <= 50'
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Error getting low performing employees: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error getting low performing employees: ' . $e->getMessage()
        ], 500);
    }
}

public function getLowPerformingEmployeesAllDivisions()
{
    try {
        \Log::info("getLowPerformingEmployeesAllDivisions called");

        // ⚠️ PERBAIKAN: Gunakan periode AKTIF yang sama dengan getAllEmployeeKpis
        $activePeriods = Period::where('kpi_published', true)
            ->where('attendance_uploaded', true)
            ->where('status', 'active') // ⚠️ FILTER HANYA YANG AKTIF
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        if ($activePeriods->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Tidak ada periode AKTIF dengan absensi dan KPI yang dipublish'
            ]);
        }

        $allLowPerformers = [];

        foreach ($activePeriods as $period) {
            \Log::info("Processing ACTIVE period for all divisions low performers: {$period->nama} (ID: {$period->id_periode})");

            // ⚠️ PERBAIKAN QUERY: Hitung score dengan rumus yang sama ×10
            $periodLowPerformers = DB::table('kpis_has_employees')
                ->where('periode_id', $period->id_periode)
                ->join('employees', 'kpis_has_employees.employees_id_karyawan', '=', 'employees.id_karyawan')
                ->select(
                    'employees.id_karyawan',
                    'employees.nama',
                    'employees.status',
                    'employees.foto',
                    'employees.no_telp as phone',
                    DB::raw('SUM(kpis_has_employees.nilai_akhir) * 10 as total_score') // ⚠️ ×10 DI SINI
                )
                ->where('employees.status', 'Aktif')
                ->groupBy('employees.id_karyawan', 'employees.nama', 'employees.status', 'employees.foto', 'employees.no_telp')
                ->having('total_score', '<=', 50) // ⚠️ FILTER: score <= 50 (setelah ×10)
                ->orderBy('total_score', 'asc')
                ->limit(10) // MAKSIMAL 10 KARYAWAN
                ->get();

            \Log::info("Low performers from period {$period->nama}:", [
                'period_id' => $period->id_periode,
                'count' => $periodLowPerformers->count()
            ]);

            foreach ($periodLowPerformers as $emp) {
                // Ambil detail divisi dan jabatan
                $employeeDetails = Employee::with(['roles.division'])->find($emp->id_karyawan);
                $division = '-';
                $position = '-';
                
                if ($employeeDetails && $employeeDetails->roles->count() > 0) {
                    $division = $employeeDetails->roles[0]->division->nama_divisi ?? '-';
                    $position = $employeeDetails->roles[0]->nama_jabatan ?? '-';
                }

                $allLowPerformers[] = [
                    'id_karyawan' => $emp->id_karyawan,
                    'nama' => $emp->nama,
                    'foto' => $emp->foto,
                    'phone' => $emp->phone,
                    'division' => $division,
                    'position' => $position,
                    'score' => floatval($emp->total_score), // ⚠️ INI SUDAH ×10
                    'period_id' => $period->id_periode,
                    'period_name' => $period->nama
                ];
            }
        }

        \Log::info("Final low performers from all divisions:", [
            'total_active_periods' => $activePeriods->count(),
            'total_low_performers' => count($allLowPerformers)
        ]);

        return response()->json([
            'success' => true,
            'data' => $allLowPerformers,
            'period_info' => [
                'total_active_periods' => $activePeriods->count(),
                'note' => 'Score = SUM(nilai_akhir) × 10, filter score <= 50, hanya periode aktif'
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Error getting low performing employees from all divisions: ' . $e->getMessage());
        \Log::error('Error trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Error getting low performing employees: ' . $e->getMessage()
        ], 500);
    }
}

    public function getDivisionKpiStats($divisionId)
    {
        try {
            \Log::info("getDivisionKpiStats called", ['division_id' => $divisionId]);

            // Ambil semua periode yang sudah dipublish (12 bulan terakhir)
            $periods = Period::where('kpi_published', true)
                ->where('status', 'active')
                ->orderBy('tanggal_mulai', 'desc')
                ->limit(12)
                ->get();

            if ($periods->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'division_average' => 0,
                        'total_employees' => 0,
                        'monthly_averages' => [],
                        'performance_distribution' => [
                            'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0
                        ],
                        'employee_scores' => []
                    ],
                    'message' => 'Tidak ada data periode'
                ]);
            }

            $monthlyAverages = [];
            $allEmployeeScores = [];

            foreach ($periods as $period) {
                // Hitung rata-rata KPI untuk divisi di periode ini
                $periodStats = DB::table('kpis_has_employees')
                    ->where('periode_id', $period->id_periode)
                    ->join('employees', 'kpis_has_employees.employees_id_karyawan', '=', 'employees.id_karyawan')
                    ->leftJoin('roles_has_employees', 'employees.id_karyawan', '=', 'roles_has_employees.employee_id')
                    ->leftJoin('roles', 'roles_has_employees.role_id', '=', 'roles.id_jabatan')
                    ->leftJoin('divisions', 'roles.division_id', '=', 'divisions.id_divisi')
                    ->select(
                        'employees.id_karyawan',
                        'employees.nama',
                        'roles.nama_jabatan as position',
                        DB::raw('SUM(kpis_has_employees.nilai_akhir) as total_score')
                    )
                    ->where('divisions.id_divisi', $divisionId)
                    ->where('employees.status', 'Aktif')
                    ->groupBy('employees.id_karyawan', 'employees.nama', 'roles.nama_jabatan')
                    ->get();

                if ($periodStats->count() > 0) {
                    $averageScore = $periodStats->avg('total_score');
                    $monthName = \Carbon\Carbon::parse($period->tanggal_mulai)->format('M Y');
                    
                    $monthlyAverages[] = [
                        'month' => $monthName,
                        'average_score' => round($averageScore, 2),
                        'total_employees' => $periodStats->count(),
                        'period_name' => $period->nama
                    ];

                    // Simpan data karyawan untuk statistik saat ini
                    if ($period->status === 'active') {
                        foreach ($periodStats as $stat) {
                            $allEmployeeScores[] = [
                                'nama' => $stat->nama,
                                'position' => $stat->position,
                                'average_score' => round($stat->total_score, 2),
                                'total_score' => round($stat->total_score, 2)
                            ];
                        }
                    }
                }
            }

            // Hitung statistik untuk periode aktif saat ini
            $currentPeriod = $periods->firstWhere('status', 'active');
            $currentStats = [];
            
            if ($currentPeriod) {
                $currentStats = DB::table('kpis_has_employees')
                    ->where('periode_id', $currentPeriod->id_periode)
                    ->join('employees', 'kpis_has_employees.employees_id_karyawan', '=', 'employees.id_karyawan')
                    ->leftJoin('roles_has_employees', 'employees.id_karyawan', '=', 'roles_has_employees.employee_id')
                    ->leftJoin('roles', 'roles_has_employees.role_id', '=', 'roles.id_jabatan')
                    ->leftJoin('divisions', 'roles.division_id', '=', 'divisions.id_divisi')
                    ->select(
                        'employees.id_karyawan',
                        DB::raw('SUM(kpis_has_employees.nilai_akhir) as total_score')
                    )
                    ->where('divisions.id_divisi', $divisionId)
                    ->where('employees.status', 'Aktif')
                    ->groupBy('employees.id_karyawan')
                    ->get();
            }

            // Hitung performance distribution untuk periode aktif
            $performanceCounts = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];
            
            foreach ($currentStats as $stat) {
                $score = $stat->total_score;
                if ($score >= 90) $performanceCounts['A']++;
                elseif ($score >= 80) $performanceCounts['B']++;
                elseif ($score >= 70) $performanceCounts['C']++;
                elseif ($score >= 60) $performanceCounts['D']++;
                else $performanceCounts['E']++;
            }

            $totalEmployees = count($currentStats);
            $divisionAverage = $totalEmployees > 0 ? $currentStats->avg('total_score') : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'division_average' => round($divisionAverage, 2),
                    'total_employees' => $totalEmployees,
                    'monthly_averages' => array_reverse($monthlyAverages), // Urut dari terlama ke terbaru
                    'performance_distribution' => $performanceCounts,
                    'employee_scores' => $allEmployeeScores
                ],
                'current_period' => $currentPeriod ? $currentPeriod->nama : 'Tidak ada periode aktif'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting division KPI stats: ' . $e->getMessage());
            \Log::error('Error trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error getting division stats: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getAvailableYears()
    {
        try {
            $years = Period::where('kpi_published', true)
                ->selectRaw('YEAR(tanggal_mulai) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year');
                
            return response()->json([
                'success' => true,
                'data' => $years
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching years: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getScoresByYear($year)
    {
        try {
            // Cari semua periode di tahun tersebut
            $periods = Period::where('kpi_published', true)
                ->whereYear('tanggal_mulai', $year)
                ->pluck('id_periode');

            if ($periods->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Tidak ada data KPI untuk tahun ' . $year
                ]);
            }

            // Ambil data KPI untuk semua periode di tahun tersebut
            $employeeScores = DB::table('kpis_has_employees')
                ->whereIn('periode_id', $periods)
                ->join('employees', 'kpis_has_employees.employees_id_karyawan', '=', 'employees.id_karyawan')
                ->select(
                    'employees.id_karyawan',
                    'employees.nama',
                    'employees.status',
                    'employees.foto',
                    DB::raw('AVG(kpis_has_employees.nilai_akhir) as avg_score') // Rata-rata score per tahun
                )
                ->where('employees.status', 'Aktif')
                ->groupBy('employees.id_karyawan', 'employees.nama', 'employees.status', 'employees.foto')
                ->orderBy('avg_score', 'desc')
                ->get();

            $formattedData = [];

            foreach ($employeeScores as $emp) {
                $employeeDetails = Employee::with(['roles.division'])->find($emp->id_karyawan);
                $division = '-';
                $position = '-';
                
                if ($employeeDetails && $employeeDetails->roles->count() > 0) {
                    $division = $employeeDetails->roles[0]->division->nama_divisi ?? '-';
                    $position = $employeeDetails->roles[0]->nama_jabatan ?? '-';
                }

                $formattedData[] = [
                'id_karyawan' => $emp->id_karyawan,
                'nama' => $emp->nama,
                'status' => $emp->status,
                'score' => floatval($emp->avg_score),
                'period' => 'Tahun ' . $year,
                'period_month' => 'Yearly',
                'period_month_number' => 0,
                'period_year' => $year,
                'photo' => $emp->foto ?? 'assets/images/profile_av.png',
                'division' => $division,
                'position' => $position,
                'period_id' => 'yearly_' . $year,
                // ⚠️ TAMBAH: Info bulan untuk yearly report
                'monthly_breakdown' => $this->getMonthlyBreakdown($emp->id_karyawan, $year)
            ];
            }

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'debug_info' => [
                    'year' => $year,
                    'periods_count' => $periods->count(),
                    'total_employees' => count($formattedData)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch KPI data by year: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch KPI data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getMonthlyBreakdown($employeeId, $year)
{
    $monthlyData = [];
    
    for ($month = 1; $month <= 12; $month++) {
        $periods = Period::where('kpi_published', true)
            ->whereYear('tanggal_mulai', $year)
            ->whereMonth('tanggal_mulai', $month)
            ->pluck('id_periode');
            
        if ($periods->isNotEmpty()) {
            $monthlyScore = DB::table('kpis_has_employees')
                ->whereIn('periode_id', $periods)
                ->where('employees_id_karyawan', $employeeId)
                ->avg('nilai_akhir');
                
            $monthlyData[] = [
                'month' => $month,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                'score' => $monthlyScore ? floatval($monthlyScore) : null
            ];
        }
    }
    
    return $monthlyData;
}
    


    private function getEmployeeKpiDetails($employeeId, $periodId)
    {
        try {
            $employee = Employee::with(['roles.division'])->find($employeeId);
            $divisionId = null;

            if ($employee->roles && count($employee->roles) > 0) {
                $divisionId = $employee->roles[0]->division_id ?? null;
            }

            // Gunakan query yang SAMA dengan getAllEmployeeKpis
            $kpis = Kpi::where('periode_id', $periodId)
                ->where(function ($query) use ($divisionId) {
                    $query->where('is_global', true);

                    if ($divisionId) {
                        $query->orWhereHas('divisions', function ($q) use ($divisionId) {
                            $q->where('divisions.id_divisi', $divisionId);
                        });
                    }
                })
                ->get();

            $details = [];

            foreach ($kpis as $kpi) {
                $score = DB::table('kpis_has_employees')
                    ->where('kpis_id_kpi', $kpi->id_kpi)
                    ->where('employees_id_karyawan', $employeeId)
                    ->where('periode_id', $periodId)
                    ->value('nilai_akhir');

                $details[] = [
                    'kpi_name' => $kpi->nama,
                    'is_global' => $kpi->is_global,
                    'score' => floatval($score) ?? 0
                ];
            }

            return $details;
        } catch (\Exception $e) {
            Log::error('Error getting KPI details: ' . $e->getMessage());
            return [];
        }
    }
  
    private function getMonthFromPeriod($startDate, $endDate)
{
    try {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        
        // Jika periode mencakup dua bulan, ambil bulan dari tanggal mulai
        // Contoh: 7 Jul 2025 - 7 Aug 2025 → return "July"
        if ($start->month != $end->month) {
            return $start->format('F'); // July
        }
        
        // Jika dalam bulan yang sama, return bulan tersebut
        return $start->format('F');
    } catch (\Exception $e) {
        return date('F'); // Fallback ke bulan sekarang
    }
}

private function getMonthNumberFromPeriod($startDate, $endDate)
{
    try {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        
        // Prioritaskan bulan dari tanggal mulai
        if ($start->month != $end->month) {
            return $start->month; // 7
        }
        
        return $start->month;
    } catch (\Exception $e) {
        return date('n'); // Fallback
    }
}

public function getNonHeadEmployeesKpis(Request $request)
{
    try {
        Log::info("=== getNonHeadEmployeesKpis - NON-HEAD in ACTIVE PERIODS ===");

        // ⚠️ PERBAIKAN: Hanya periode AKTIF
        $activePeriods = Period::where('kpi_published', true)
            ->where('attendance_uploaded', true)
            ->where('status', 'active') // ⚠️ FILTER HANYA YANG AKTIF
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        Log::info("Active periods found: " . $activePeriods->count());

        if ($activePeriods->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Tidak ada periode AKTIF dengan absensi dan KPI yang dipublish'
            ]);
        }

        $allEmployeeData = [];

        foreach ($activePeriods as $period) {
            Log::info("Processing ACTIVE period: {$period->nama} (ID: {$period->id_periode})");

            // 1. Ambil semua karyawan yang AKTIF dan BUKAN Kepala Divisi
            $nonHeadEmployees = DB::table('employees')
                ->join('roles_has_employees', 'employees.id_karyawan', '=', 'roles_has_employees.employee_id')
                ->join('roles', 'roles_has_employees.role_id', '=', 'roles.id_jabatan')
                ->select(
                    'employees.id_karyawan',
                    'employees.nama',
                    'employees.status',
                    'employees.foto',
                    'roles.nama_jabatan as position',
                    'roles.division_id'
                )
                ->where('employees.status', 'Aktif')
                ->where('roles.nama_jabatan', 'not like', '%Kepala Divisi%')
                ->get();

            Log::info("Non-head employees found: " . $nonHeadEmployees->count());

            foreach ($nonHeadEmployees as $emp) {
                // 2. CEK APAKAH MEMILIKI ABSENSI DI PERIODE INI
                $hasAttendance = DB::table('attendances')
                    ->where('employee_id', $emp->id_karyawan)
                    ->where('periode_id', $period->id_periode)
                    ->exists();

                if (!$hasAttendance) {
                    Log::info("No attendance for employee {$emp->id_karyawan} in period {$period->id_periode}");
                    continue;
                }

                // 3. ✅ CUMA TAMPILIN YANG BELUM DINILAI
                $hasKpiScore = DB::table('kpis_has_employees')
                    ->where('employees_id_karyawan', $emp->id_karyawan)
                    ->where('periode_id', $period->id_periode)
                    ->where('nilai_akhir', '>', 0)
                    ->exists();

                if ($hasKpiScore) {
                    Log::info("Employee {$emp->id_karyawan} already has KPI score in period {$period->id_periode}");
                    continue;
                }

                // Ambil detail divisi
                $division = '-';
                $divisionId = $emp->division_id;
                
                if ($divisionId) {
                    $divisionData = DB::table('divisions')
                        ->where('id_divisi', $divisionId)
                        ->first();
                    $division = $divisionData ? $divisionData->nama_divisi : '-';
                }

                $periodMonth = date('F', strtotime($period->tanggal_mulai));
                $periodYear = date('Y', strtotime($period->tanggal_mulai));

                // ✅ PERBAIKAN PATH FOTO
                $fotoPath = $emp->foto;
                if (!$fotoPath || $fotoPath === '' || $fotoPath === 'null') {
                    $fotoPath = 'assets/images/profile_av.png';
                } else {
                    // Handle foto path yang proper
                    if (filter_var($fotoPath, FILTER_VALIDATE_URL)) {
                        // Already a full URL
                    } elseif (strpos($fotoPath, 'storage/') === 0) {
                        $fotoPath = asset($fotoPath);
                    } elseif (strpos($fotoPath, 'profile-photos/') === 0) {
                        $fotoPath = asset('storage/' . $fotoPath);
                    } else {
                        $fotoPath = asset('storage/' . $fotoPath);
                    }
                }

                $employeeData = [
                    'id_karyawan' => $emp->id_karyawan,
                    'nama' => $emp->nama,
                    'status' => $emp->status,
                    'score' => 0,
                    'period' => $period->nama,
                    'period_month' => $periodMonth,
                    'period_month_number' => date('n', strtotime($period->tanggal_mulai)),
                    'period_year' => $periodYear,
                    'photo' => $fotoPath,
                    'division' => $division,
                    'division_id' => $divisionId,
                    'position' => $emp->position,
                    'period_id' => $period->id_periode,
                    'employee_type' => 'non_head'
                ];

                $allEmployeeData[] = $employeeData;
                Log::info("✅ Added UNRATED NON-HEAD employee:", [
                    'nama' => $emp->nama,
                    'period' => $period->nama
                ]);
            }
        }

        Log::info("Final UNRATED NON-HEAD employees in ACTIVE periods:", [
            'total_unrated_non_head' => count($allEmployeeData),
            'active_periods_processed' => $activePeriods->count()
        ]);

        return response()->json([
            'success' => true,
            'data' => $allEmployeeData,
            'debug_info' => [
                'total_active_periods' => $activePeriods->count(),
                'total_unrated_non_head_employees' => count($allEmployeeData),
                'note' => 'Hanya menampilkan karyawan NON-Kepala Divisi yang BELUM dinilai di periode AKTIF'
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to fetch UNRATED NON-HEAD KPI data: ' . $e->getMessage());
        Log::error('Error trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch NON-HEAD KPI data: ' . $e->getMessage(),
            'error_details' => $e->getTraceAsString() // Untuk debugging
        ], 500);
    }
}

public function exportMonthlyKpi($employeeId, $year = null)
{
    try {
        \Log::info("=== EXPORT MONTHLY KPI - SYNCHRONIZED WITH TABLE ===");

        $employee = Employee::with(['roles.division'])->find($employeeId);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Dapatkan divisi karyawan
        $employeeDivisionId = null;
        if ($employee->roles && count($employee->roles) > 0) {
            $employeeDivisionId = $employee->roles[0]->division_id ?? null;
        }

        $exportYear = $year ?: date('Y');

        // Ambil semua periode yang sudah dipublish untuk tahun tersebut
        $periods = Period::where('kpi_published', true)
            ->whereYear('tanggal_mulai', $exportYear)
            ->orderBy('tanggal_mulai', 'asc')
            ->get();

        if ($periods->isEmpty()) {
            return response("
                <script>
                    alert('Tidak ada data KPI untuk tahun {$exportYear}');
                    window.history.back();
                </script>
            ");
        }

        $exportData = [];
        $monthlyTotals = [];
        $allSubAspekNames = [];

        foreach ($periods as $period) {
            $monthName = \Carbon\Carbon::parse($period->tanggal_mulai)->format('F Y');
            $monthKey = \Carbon\Carbon::parse($period->tanggal_mulai)->format('Y-m');
            
            // ✅ PAKAI METHOD YANG SUDAH DIPERBAIKI
            $kpiData = $this->getKpiDataByDivision($employeeId, $period->id_periode, $employeeDivisionId);
            
            $monthTotal = 0;

            foreach ($kpiData as $subAspek) {
                $subAspekName = $subAspek['sub_aspek_name'];
                $score = $subAspek['score']; // Sudah dalam bentuk kontribusi
                $aspekUtama = $subAspek['aspek_utama'];
                $bobot = $subAspek['bobot'];
                
                // Simpan semua nama sub aspek
                $fullName = "{$aspekUtama} - {$subAspekName}";
                if (!in_array($fullName, $allSubAspekNames)) {
                    $allSubAspekNames[] = $fullName;
                }
                
                // Simpan data per sub aspek per bulan
                if (!isset($exportData[$fullName])) {
                    $exportData[$fullName] = [
                        'aspek_utama' => $aspekUtama,
                        'sub_aspek_name' => $subAspekName,
                        'full_name' => $fullName,
                        'bobot' => $bobot,
                        'raw_scores' => [], // Simpan raw score untuk debug
                        'scores' => []
                    ];
                }
                
                $exportData[$fullName]['scores'][$monthKey] = [
                    'score' => $score,
                    'month_name' => $monthName
                ];
                
                $exportData[$fullName]['raw_scores'][$monthKey] = $subAspek['raw_score'] ?? 0;
                
                $monthTotal += $score;
            }

            $monthlyTotals[$monthKey] = [
                'total' => $monthTotal,
                'month_name' => $monthName
            ];

            \Log::info("📅 MONTHLY TOTAL CALCULATED:", [
                'month' => $monthName,
                'total_score' => $monthTotal,
                'period_id' => $period->id_periode
            ]);
        }

        // ✅ VERIFIKASI: Bandingkan dengan nilai di tabel
        $this->verifyExportWithTable($employeeId, $exportYear, $monthlyTotals);

        if (empty($exportData)) {
            return response("
                <script>
                    alert('Tidak ada data KPI yang ditemukan untuk karyawan ini di divisinya');
                    window.history.back();
                </script>
            ");
        }

        // Format data untuk export
        $pivotedData = $this->formatExportDataWithSubAspek($exportData, $monthlyTotals, $allSubAspekNames);

        // Generate filename
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $employee->nama);
        $fileName = "KPI_Detail_{$safeName}_{$exportYear}.xlsx";

        return Excel::download(
            new MonthlyKpiExport($employee, $pivotedData, $exportYear),
            $fileName
        );

    } catch (\Exception $e) {
        \Log::error("EXPORT ERROR: " . $e->getMessage());
        \Log::error("Stack trace: " . $e->getTraceAsString());
        
        return response("
            <script>
                alert('Error saat mengekspor data: " . addslashes($e->getMessage()) . "');
                window.history.back();
            </script>
        ");
    }
}

// ✅ METHOD BARU UNTUK VERIFIKASI
private function verifyExportWithTable($employeeId, $year, $monthlyTotals)
{
    try {
        // Ambil data dari API yang sama dengan tabel
        $tableData = DB::table('kpis_has_employees')
            ->where('employees_id_karyawan', $employeeId)
            ->whereYear('created_at', $year)
            ->select('periode_id', DB::raw('SUM(nilai_akhir) as total_score'))
            ->groupBy('periode_id')
            ->get()
            ->keyBy('periode_id');

        \Log::info("🔍 VERIFICATION - Table vs Export:", [
            'employee_id' => $employeeId,
            'year' => $year,
            'table_data' => $tableData->toArray(),
            'export_totals' => $monthlyTotals
        ]);

        foreach ($monthlyTotals as $monthKey => $exportTotal) {
            $periodId = Period::whereYear('tanggal_mulai', $year)
                ->where(\DB::raw("DATE_FORMAT(tanggal_mulai, '%Y-%m')"), $monthKey)
                ->value('id_periode');

            if ($periodId && isset($tableData[$periodId])) {
                $tableScore = $tableData[$periodId]->total_score;
                $exportScore = $exportTotal['total'];
                $difference = abs($tableScore - $exportScore);

                \Log::info("📊 SCORE COMPARISON:", [
                    'month' => $monthKey,
                    'period_id' => $periodId,
                    'table_score' => $tableScore,
                    'export_score' => $exportScore,
                    'difference' => $difference
                ]);

                if ($difference > 0.01) { // Toleransi kecil untuk floating point
                    \Log::warning("⚠️ SCORE MISMATCH DETECTED:", [
                        'month' => $monthKey,
                        'table' => $tableScore,
                        'export' => $exportScore,
                        'difference' => $difference
                    ]);
                }
            }
        }

    } catch (\Exception $e) {
        \Log::error("Verification error: " . $e->getMessage());
    }
}



// Di KpiEvaluationController.php - method getEmployeeKpiDetail
private function getKpiDataWithSubAspek($employeeId, $periodId)
{
    $kpiData = [];
    
    // Ambil semua KPI untuk employee di periode ini
    $kpis = Kpi::where('periode_id', $periodId)
        ->with(['points.questions'])
        ->get();

    foreach ($kpis as $kpi) {
        $aspekUtama = $kpi->nama;
        $kpiBobot = floatval($kpi->bobot);
        
        // Ambil nilai akhir KPI dari tabel
        $kpiFinalScore = DB::table('kpis_has_employees')
            ->where('kpis_id_kpi', $kpi->id_kpi)
            ->where('employees_id_karyawan', $employeeId)
            ->where('periode_id', $periodId)
            ->value('nilai_akhir');

        // 1. TOTAL ASPEK UTAMA
        if ($kpiFinalScore) {
            $kpiData[] = [
                'aspek_kpi' => $aspekUtama,
                'sub_aspek_name' => 'TOTAL_ASPEK',
                'score' => floatval($kpiFinalScore),
                'bobot' => $kpiBobot,
                'is_total_aspek' => true
            ];
        }

        // 2. DETAIL SUB-ASPEK
        $kpisHasEmployeeId = DB::table('kpis_has_employees')
            ->where('kpis_id_kpi', $kpi->id_kpi)
            ->where('employees_id_karyawan', $employeeId)
            ->where('periode_id', $periodId)
            ->value('id');

        foreach ($kpi->points as $point) {
            $subAspekName = $point->nama;
            $pointScore = 0;
            $isAbsensi = stripos($point->nama, 'absensi') !== false;

            if ($isAbsensi && $kpisHasEmployeeId) {
                // Untuk absensi
                $pointRecord = DB::table('kpi_points_has_employee')
                    ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                    ->where('kpi_point_id', $point->id_point)
                    ->first();
                $pointScore = $pointRecord->nilai_absensi ?? 0;
            } else {
                // Untuk non-absensi
                $pointTotal = 0;
                $answeredQuestions = 0;

                foreach ($point->questions as $question) {
                    $answer = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                        ->where('kpi_question_id_question', $question->id_question)
                        ->where('periode_id', $periodId)
                        ->first();

                    if ($answer && $answer->nilai !== null) {
                        $questionScore = (($answer->nilai - 1) / 3) * 100;
                        $pointTotal += $questionScore;
                        $answeredQuestions++;
                    }
                }
                $pointScore = $answeredQuestions > 0 ? ($pointTotal / $answeredQuestions) : 0;
            }

            $pointBobot = floatval($point->bobot) ?? 0;
            
            $kpiData[] = [
                'aspek_kpi' => $aspekUtama,
                'sub_aspek_name' => $subAspekName,
                'score' => $pointScore,
                'bobot' => $pointBobot,
                'is_total_aspek' => false
            ];
        }
    }

    return $kpiData;
}

private function formatExportDataWithSubAspek($exportData, $monthlyTotals, $allSubAspekNames)
{
    // Urutkan bulan
    $sortedMonths = array_keys($monthlyTotals);
    usort($sortedMonths, function($a, $b) {
        return strtotime($a) - strtotime($b);
    });

    // Format nama bulan untuk display
    $formattedMonths = [];
    foreach ($sortedMonths as $monthKey) {
        $formattedMonths[$monthKey] = $monthlyTotals[$monthKey]['month_name'];
    }

    // Siapkan data scores
    $scores = [];
    foreach ($allSubAspekNames as $fullName) {
        if (isset($exportData[$fullName])) {
            $subAspekData = $exportData[$fullName];
            $scores[$fullName] = [];
            
            foreach ($sortedMonths as $monthKey) {
                $score = $subAspekData['scores'][$monthKey]['score'] ?? 0;
                $scores[$fullName][$monthKey] = $score;
            }
        }
    }

    // Siapkan data totals
    $totals = [];
    foreach ($sortedMonths as $monthKey) {
        $totals[$monthKey] = $monthlyTotals[$monthKey]['total'] ?? 0;
    }

    // Siapkan grouping by aspek utama
    $groupedData = [];
    foreach ($exportData as $fullName => $data) {
        $aspekUtama = $data['aspek_utama'];
        if (!isset($groupedData[$aspekUtama])) {
            $groupedData[$aspekUtama] = [];
        }
        $groupedData[$aspekUtama][] = $fullName;
    }

    return [
        'points' => $exportData, // Semua data sub aspek
        'months' => $formattedMonths,
        'scores' => $scores,
        'totals' => $totals,
        'sorted_months' => $sortedMonths,
        'grouped_data' => $groupedData // Untuk grouping di Excel
    ];
}

private function getKpiDataByDivision($employeeId, $periodId, $employeeDivisionId)
{
    $kpiData = [];
    
    $kpis = Kpi::where('periode_id', $periodId)
        ->where(function ($query) use ($employeeDivisionId) {
            if ($employeeDivisionId) {
                $query->whereHas('divisions', function ($q) use ($employeeDivisionId) {
                    $q->where('divisions.id_divisi', $employeeDivisionId);
                });
            }
            if (!$query->getQuery()->wheres) {
                $query->orWhere('is_global', true);
            }
        })
        ->with(['points.questions'])
        ->get();

    foreach ($kpis as $kpi) {
        $aspekUtama = $kpi->nama;
        
        $kpisHasEmployeeId = DB::table('kpis_has_employees')
            ->where('kpis_id_kpi', $kpi->id_kpi)
            ->where('employees_id_karyawan', $employeeId)
            ->where('periode_id', $periodId)
            ->value('id');

        foreach ($kpi->points as $point) {
            $subAspekName = $point->nama;
            $pointScore = 0;
            $isAbsensi = stripos($point->nama, 'absensi') !== false 
                      || stripos($point->nama, 'kehadiran') !== false;

            // ✅ RUMUS BARU: (rata-rata × 2.5) × bobot
            if ($isAbsensi && $kpisHasEmployeeId) {
                $pointRecord = DB::table('kpi_points_has_employee')
                    ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                    ->where('kpi_point_id', $point->id_point)
                    ->first();

                $pointScore = ($pointRecord->nilai_absensi * floatval($point->bobot)) / 100;
                
            } else if ($point->questions->count() > 0) {
                $pointTotal = 0;
                $answeredQuestions = 0;

                foreach ($point->questions as $question) {
                    $answer = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                        ->where('kpi_question_id_question', $question->id_question)
                        ->where('periode_id', $periodId)
                        ->first();

                    if ($answer && $answer->nilai !== null) {
                        $pointTotal += $answer->nilai;
                        $answeredQuestions++;
                    }
                }

                if ($answeredQuestions > 0) {
                    $avgQuestionScore = $pointTotal / $answeredQuestions;
                    // RUMUS BARU: (rata-rata × 2.5) × (bobot / 100)
                    $pointScore = ($avgQuestionScore * 2.5) * (floatval($point->bobot) / 100);
                }
            } else {
                continue;
            }

            $kpiData[] = [
                'aspek_utama' => $aspekUtama,
                'sub_aspek_name' => $subAspekName,
                'score' => $pointScore, // ✅ Sudah dalam bentuk kontribusi final
                'bobot' => floatval($point->bobot),
                'raw_score' => $pointScore // Untuk debug
            ];
        }
    }

    // ✅ HITUNG TOTAL (SAMA DENGAN CARA TABEL)
    $totalScore = array_sum(array_column($kpiData, 'score'));
    
    \Log::info("🎯 FINAL EXPORT DATA - NEW FORMULA:", [
        'total_points' => count($kpiData),
        'total_score' => $totalScore,
        'data' => $kpiData
    ]);

    return $kpiData;
}

private function getKpiDetailsFromDatabase($employeeId, $periodId)
{
    $kpiData = [];
    
    $employee = Employee::with(['roles.division'])->find($employeeId);
    $divisionId = $employee->roles->first()->division_id ?? null;

    $kpis = Kpi::where('periode_id', $periodId)
        ->where(function ($query) use ($divisionId) {
            $query->where('is_global', true);
            if ($divisionId) {
                $query->orWhereHas('divisions', function ($q) use ($divisionId) {
                    $q->where('divisions.id_divisi', $divisionId);
                });
            }
        })
        ->with(['points.questions'])
        ->get();

    foreach ($kpis as $kpi) {
        $aspekUtama = $kpi->nama;
        
        // Ambil nilai akhir KPI dari database
        $kpiFinalScore = DB::table('kpis_has_employees')
            ->where('kpis_id_kpi', $kpi->id_kpi)
            ->where('employees_id_karyawan', $employeeId)
            ->where('periode_id', $periodId)
            ->value('nilai_akhir');

        // TOTAL ASPEK UTAMA
        $kpiData[] = [
            'aspek_kpi' => $aspekUtama,
            'sub_aspek_name' => 'TOTAL ASPEK',
            'score' => floatval($kpiFinalScore) ?? 0,
            'bobot' => floatval($kpi->bobot),
            'kontribusi' => floatval($kpiFinalScore) ?? 0,
            'is_total_aspek' => true
        ];

        // DETAIL SUB-ASPEK
        $kpisHasEmployeeId = DB::table('kpis_has_employees')
            ->where('kpis_id_kpi', $kpi->id_kpi)
            ->where('employees_id_karyawan', $employeeId)
            ->where('periode_id', $periodId)
            ->value('id');

        foreach ($kpi->points as $point) {
            $pointScore = 0;
            $isAbsensi = stripos($point->nama, 'absensi') !== false;

            if ($isAbsensi && $kpisHasEmployeeId) {
                $pointRecord = DB::table('kpi_points_has_employee')
                    ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                    ->where('kpi_point_id', $point->id_point)
                    ->first();
                // RUMUS BARU: (nilai_absensi × bobot) / 100
                $pointScore = ($pointRecord->nilai_absensi * floatval($point->bobot)) / 100;
            } else {
                $pointTotal = 0;
                $answeredQuestions = 0;

                foreach ($point->questions as $question) {
                    $answer = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                        ->where('kpi_question_id_question', $question->id_question)
                        ->where('periode_id', $periodId)
                        ->first();

                    if ($answer && $answer->nilai !== null) {
                        $pointTotal += $answer->nilai;
                        $answeredQuestions++;
                    }
                }
                // RUMUS BARU: (rata-rata × 2.5) × (bobot / 100)
                if ($answeredQuestions > 0) {
                    $avgQuestionScore = $pointTotal / $answeredQuestions;
                    $pointScore = ($avgQuestionScore * 2.5) * (floatval($point->bobot) / 100);
                }
            }

            $kpiData[] = [
                'aspek_kpi' => $aspekUtama,
                'sub_aspek_name' => $point->nama,
                'score' => $pointScore,
                'bobot' => floatval($point->bobot),
                'kontribusi' => $pointScore,
                'is_total_aspek' => false
            ];
        }
    }

    return $kpiData;
}

    private function getStatusByContribution($contribution)
    {
        $numericContribution = floatval($contribution);

        if ($numericContribution >= 90) return 'Sangat Baik';
        if ($numericContribution >= 80) return 'Baik'; 
        if ($numericContribution >= 70) return 'Cukup';
        if ($numericContribution >= 50) return 'Kurang';
        return 'Sangat Kurang';
    }

        // STANDARDISASI UNTUK SEMUA METHOD
    private function getScoreStatus($score)
    {
        $numericScore = floatval($score);
        
        if ($numericScore >= 90) return 'Sangat Baik';
        if ($numericScore >= 80) return 'Baik';
        if ($numericScore >= 70) return 'Cukup'; 
        if ($numericScore >= 50) return 'Kurang';
        return 'Sangat Kurang';
    }

        // STANDARDISASI UNTUK GRADE HURUF
    private function getLetterGrade($score)
    {
        $numericScore = floatval($score);
        
        if ($numericScore >= 90) return 'A';
        if ($numericScore >= 80) return 'B';
        if ($numericScore >= 70) return 'C';
        if ($numericScore >= 50) return 'D';
        return 'E';
    } 

    public function getTopEmployees($limit = 3)
{
    try {
        $latestPeriod = Period::where('kpi_published', true)
            ->where('status', 'active')
            ->orderBy('tanggal_mulai', 'desc')
            ->first();

        if (!$latestPeriod) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Tidak ada periode aktif dengan KPI yang dipublish'
            ]);
        }

        // Ambil karyawan dengan score tertinggi
        $topEmployees = DB::table('kpis_has_employees')
            ->where('periode_id', $latestPeriod->id_periode)
            ->join('employees', 'kpis_has_employees.employees_id_karyawan', '=', 'employees.id_karyawan')
            ->select(
                'employees.id_karyawan',
                'employees.nama',
                'employees.status',
                'employees.foto',
                DB::raw('SUM(kpis_has_employees.nilai_akhir) * 10 as total_score')
            )
            ->where('employees.status', 'Aktif')
            ->groupBy('employees.id_karyawan', 'employees.nama', 'employees.status', 'employees.foto')
            ->orderBy('total_score', 'desc')
            ->limit($limit)
            ->get();

        $formattedData = [];

        foreach ($topEmployees as $emp) {
            $employeeDetails = Employee::with(['roles.division'])->find($emp->id_karyawan);
            $division = '-';
            $position = '-';
            
            if ($employeeDetails && $employeeDetails->roles->count() > 0) {
                $division = $employeeDetails->roles[0]->division->nama_divisi ?? '-';
                $position = $employeeDetails->roles[0]->nama_jabatan ?? '-';
            }

            $formattedData[] = [
                'id_karyawan' => $emp->id_karyawan,
                'nama' => $emp->nama,
                'status' => $emp->status,
                'score' => floatval($emp->total_score),
                'period' => $latestPeriod->nama,
                'period_month' => date('F', strtotime($latestPeriod->tanggal_mulai)),
                'period_year' => date('Y', strtotime($latestPeriod->tanggal_mulai)),
                'photo' => $emp->foto ? asset('storage/' . $emp->foto) : asset('assets/images/profile_av.png'),
                'division' => $division,
                'position' => $position,
                'period_id' => $latestPeriod->id_periode
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $formattedData,
            'period_info' => [
                'id' => $latestPeriod->id_periode,
                'nama' => $latestPeriod->nama,
                'tanggal_mulai' => $latestPeriod->tanggal_mulai
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to fetch top employees: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch top employees: ' . $e->getMessage()
        ], 500);
    }
}

// ⚠️ METHOD BARU: Ambil hanya aspek utama saja (tanpa sub-aspek)
public function getEmployeeKpiAspekOnly($employeeId, $periodId = null)
{
    try {
        Log::info("getEmployeeKpiAspekOnly called:", [
            'employee_id' => $employeeId,
            'period_id' => $periodId
        ]);

        $employee = Employee::with(['roles.division'])->findOrFail($employeeId);
        
        if (!$periodId) {
            $activePeriod = Period::where('status', 'active')
                ->where('kpi_published', true)
                ->first();
            
            if (!$activePeriod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada periode aktif'
                ], 404);
            }
            $periodId = $activePeriod->id_periode;
        }

        $period = Period::findOrFail($periodId);

        // ⚠️ HITUNG TOTAL SCORE DENGAN RUMUS YANG SAMA
        $totalScore = $this->calculateTotalScoreWithNewFormula($employeeId, $periodId);

        // ⚠️ METHOD BARU: Ambil hanya aspek utama saja
        $kpiAspekOnly = $this->getKpiAspekOnly($employeeId, $periodId);

        // Hitung ranking (sama seperti sebelumnya)
        $allEmployeeScores = DB::table('kpis_has_employees')
            ->where('periode_id', $periodId)
            ->select('employees_id_karyawan', DB::raw('SUM(nilai_akhir) * 10 as total_score'))
            ->groupBy('employees_id_karyawan')
            ->orderBy('total_score', 'desc')
            ->get();

        $ranking = 1;
        foreach ($allEmployeeScores as $empScore) {
            if ($empScore->employees_id_karyawan == $employeeId) break;
            $ranking++;
        }

        $totalEmployees = $allEmployeeScores->count();

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => [
                    'id_karyawan' => $employee->id_karyawan,
                    'nama' => $employee->nama,
                    'division' => $employee->roles->first()->division->nama_divisi ?? '-',
                    'position' => $employee->roles->first()->nama_jabatan ?? '-'
                ],
                'period' => $period,
                'kpi_summary' => [
                    'total_score' => round($totalScore, 2),
                    'average_score' => round($totalScore, 2),
                    'average_contribution' => round($totalScore, 2),
                    'performance_status' => $this->getStatusByContribution($totalScore),
                    'ranking' => $ranking,
                    'total_employees' => $totalEmployees
                ],
                'kpi_aspek_only' => $kpiAspekOnly // ⚠️ HANYA ASPEK SAJA
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error getting employee KPI aspek only: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error getting KPI aspek: ' . $e->getMessage()
        ], 500);
    }
}

// ⚠️ METHOD BARU: Ambil hanya aspek utama tanpa detail sub-aspek
private function getKpiAspekOnly($employeeId, $periodId)
{
    $kpiData = [];
    
    $employee = Employee::with(['roles.division'])->find($employeeId);
    $divisionId = $employee->roles->first()->division_id ?? null;

    $kpis = Kpi::where('periode_id', $periodId)
        ->where(function ($query) use ($divisionId) {
            $query->where('is_global', true);
            if ($divisionId) {
                $query->orWhereHas('divisions', function ($q) use ($divisionId) {
                    $q->where('divisions.id_divisi', $divisionId);
                });
            }
        })
        ->with(['points.questions'])
        ->get();

    foreach ($kpis as $kpi) {
        $aspekUtama = $kpi->nama;
        $totalAspekScore = 0;

        // Hitung total score untuk aspek ini
        foreach ($kpi->points as $point) {
            $pointScore = 0;
            $isAbsensi = stripos($point->nama, 'absensi') !== false;

            if ($isAbsensi) {
                $kpisHasEmployeeId = DB::table('kpis_has_employees')
                    ->where('kpis_id_kpi', $kpi->id_kpi)
                    ->where('employees_id_karyawan', $employeeId)
                    ->where('periode_id', $periodId)
                    ->value('id');

                if ($kpisHasEmployeeId) {
                    $pointRecord = DB::table('kpi_points_has_employee')
                        ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                        ->where('kpi_point_id', $point->id_point)
                        ->first();

                    if ($pointRecord) {
                        $pointScore = ($pointRecord->nilai_absensi * floatval($point->bobot)) / 100;
                    }
                }
            } else {
                $pointTotal = 0;
                $answeredQuestions = 0;

                foreach ($point->questions as $question) {
                    $answer = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                        ->where('kpi_question_id_question', $question->id_question)
                        ->where('periode_id', $periodId)
                        ->first();

                    if ($answer && $answer->nilai !== null) {
                        $pointTotal += $answer->nilai;
                        $answeredQuestions++;
                    }
                }

                if ($answeredQuestions > 0) {
                    $avgQuestionScore = $pointTotal / $answeredQuestions;
                    $pointScore = ($avgQuestionScore * 2.5) * (floatval($point->bobot) / 100);
                }
            }

            $totalAspekScore += $pointScore;
        }

        $kpiData[] = [
            'aspek_kpi' => $aspekUtama,
            'score' => $totalAspekScore * 10, // ⚠️ SUDAH ×10
            'bobot' => floatval($kpi->bobot),
            'kontribusi' => $totalAspekScore,
            'performance_status' => $this->getStatusByContribution($totalAspekScore * 10)
        ];
    }

    return $kpiData;
}
}
