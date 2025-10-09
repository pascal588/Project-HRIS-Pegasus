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
        // ================== KPI EVALUATION & SCORING ==================
    public function storeEmployeeScore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_karyawan' => 'required|exists:employees,id_karyawan',
            'periode_id' => 'required|exists:periods,id_periode',
            'hasil' => 'required|array|min:1',
            'hasil.*.id_aspek' => 'required|exists:kpis,id_kpi',
            'hasil.*.jawaban' => 'required|array|min:1',
            'hasil.*.jawaban.*.id' => 'required', // Hapus exists validation sementara untuk debugging
            'hasil.*.jawaban.*.jawaban' => 'required|integer|min:1|max:4',
            'attendance_scores' => 'sometimes|array',
            'attendance_scores.*.point_id' => 'required|exists:kpi_points,id_point',
            'attendance_scores.*.score' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // ⚠️ TAMBAH: Log data yang diterima
        Log::info("=== STORE EMPLOYEE SCORE REQUEST ===");
        Log::info("Employee ID: " . $request->id_karyawan);
        Log::info("Period ID: " . $request->periode_id);
        Log::info("Attendance Scores: " . json_encode($request->attendance_scores));
        Log::info("Hasil: " . json_encode($request->hasil));

        $tahun = $period->tahun ?? date('Y');
        $bulan = $period->bulan ?? date('m');
        $employeeId = $request->id_karyawan;
        $periodeId = $request->periode_id;

        DB::beginTransaction();
        try {
            $savedCount = 0;

            Log::info("=== START KPI SCORE SAVE ===");
            Log::info("Employee ID: $employeeId, Periode ID: $periodeId");

            // 1️⃣ SIMPAN NILAI ABSENSI OTOMATIS (JIKA ADA)
            if (isset($request->attendance_scores)) {
                foreach ($request->attendance_scores as $attendanceScore) {
                    $saved = $this->saveAttendanceScore(
                        $employeeId,
                        $periodeId,
                        $attendanceScore['point_id'],
                        $attendanceScore['score']
                    );

                    if ($saved) {
                        Log::info("Attendance score saved successfully", $attendanceScore);
                        $savedCount++;
                    }
                }
            }

            // 2️⃣ SIMPAN JAWABAN PERTANYAAN NORMAL
            foreach ($request->hasil as $aspekIndex => $aspek) {
                foreach ($aspek['jawaban'] as $jawaban) {
                    $saved = KpiQuestionHasEmployee::updateOrCreate(
                        [
                            'employees_id_karyawan' => $employeeId,
                            'kpi_question_id_question' => $jawaban['id'],
                            'periode_id' => $periodeId
                        ],
                        ['nilai' => $jawaban['jawaban'], 'updated_at' => now()]
                    );

                    if ($saved) $savedCount++;
                }
            }

            // 3️⃣ HITUNG NILAI AKHIR UNTUK SEMUA ASPEK (TERMASUK ABSENSI)
            $this->calculateAllFinalScores($employeeId, $periodeId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jawaban dan nilai akhir KPI berhasil disimpan!',
                'saved_count' => $savedCount,
                'attendance_scores_saved' => isset($request->attendance_scores) ? count($request->attendance_scores) : 0
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save scores: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan nilai: ' . $e->getMessage()
            ], 500);
        }
    }

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

public function getAttendanceCalculationData($employeeId, $periodeId)
{
    try {
        $employee = Employee::findOrFail($employeeId);
        $period = Period::findOrFail($periodeId);

        // ⚠️ DEBUG: Cek data absensi yang diambil
        $attendances = Attendance::where('employee_id', $employeeId)
            ->where('periode_id', $periodeId)
            ->get();

        Log::info("📊 ATTENDANCE DATA DEBUG:", [
            'employee_id' => $employeeId,
            'period_id' => $periodeId,
            'total_attendance_records' => $attendances->count(),
            'attendance_samples' => $attendances->take(5)->map(function($att) {
                return [
                    'date' => $att->tanggal,
                    'status' => $att->status,
                    'late' => $att->late
                ];
            })
        ]);

        $workDays = $attendances->where('status', '!=', 'Non-working day (NW)');
        $totalWorkDays = $workDays->count();

        // Hitung summary
        $hadir = $workDays->where('status', 'Present at workday (PW)')->count();
        $sakit = $workDays->where('status', 'Sick (S)')->count();
        $izin = $workDays->where('status', 'Permission (I)')->count();
        $mangkir = $workDays->where('status', 'Absent (A)')->count();
        $terlambat = $workDays->where('late', '>', 0)->count();

        // ⚠️ PERBAIKAN: DEFINE $config SEBELUM DIPAKAI!
        $config = $this->getAttendanceConfigFromKpiTemplate($employeeId, $periodeId);

        Log::info("🎯 ATTENDANCE CALCULATION:", [
            'hadir' => $hadir,
            'sakit' => $sakit,
            'izin' => $izin,
            'mangkir' => $mangkir,
            'terlambat' => $terlambat,
            'total_work_days' => $totalWorkDays,
            'config_used' => $config
        ]);

        // Hitung berdasarkan konfigurasi
        $kehadiranPoints = $hadir * $config['hadir_multiplier'];
        $sakitPoints = $sakit * $config['sakit_multiplier'];
        $izinPoints = $izin * $config['izin_multiplier'];
        $mangkirPoints = $mangkir * $config['mangkir_multiplier'];
        $subTotal = $kehadiranPoints + $sakitPoints + $izinPoints + $mangkirPoints;

        $terlambatPoints = $terlambat * $config['terlambat_multiplier'];
        $totalPointsX = $subTotal + $terlambatPoints;

        $maxPointsY = $totalWorkDays * $config['workday_multiplier'];

        $attendancePercent = $maxPointsY > 0 ? ($totalPointsX / $maxPointsY) * 100 : 0;

        // Konversi ke skala 0-100
        if ($attendancePercent >= 100) $score = 100;
        elseif ($attendancePercent >= 90) $score = 80;
        elseif ($attendancePercent >= 80) $score = 60;
        elseif ($attendancePercent >= 65) $score = 40;
        elseif ($attendancePercent >= 50) $score = 20;
        else $score = 0;

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => [
                    'id' => $employee->id_karyawan,
                    'nama' => $employee->nama
                ],
                'period' => $period->nama,
                'attendance_data' => [
                    'hadir' => $hadir,
                    'sakit' => $sakit,
                    'izin' => $izin,
                    'mangkir' => $mangkir,
                    'terlambat' => $terlambat,
                    'total_days' => $attendances->count(),
                    'total_work_days' => $totalWorkDays,
                    'libur' => $attendances->where('status', 'Non-working day (NW)')->count()
                ],
                'calculation' => [
                    'kehadiran_points' => $kehadiranPoints,
                    'sakit_points' => $sakitPoints,
                    'izin_points' => $izinPoints,
                    'mangkir_points' => $mangkirPoints,
                    'sub_total' => $subTotal,
                    'terlambat_points' => $terlambatPoints,
                    'total_points_x' => $totalPointsX,
                    'max_points_y' => $maxPointsY,
                    'attendance_percent' => round($attendancePercent, 2),
                    'final_score' => $score
                ],
                'config' => $config
            ]
        ]);
    } catch (\Exception $e) {
        Log::error('Error calculating attendance data: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error calculating attendance data: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Ambil konfigurasi absensi dari KPI template
 */
private function getAttendanceConfigFromKpiTemplate($employeeId, $periodeId)
{
    try {
        $employee = Employee::with(['roles.division'])->find($employeeId);
        $divisionId = $employee->roles->first()->division_id ?? null;

        // Cari di KPI Aktif (dengan periode_id)
        $kpis = Kpi::where('periode_id', $periodeId)
            ->where(function ($query) use ($divisionId) {
                $query->where('is_global', true);
                if ($divisionId) {
                    $query->orWhereHas('divisions', function ($q) use ($divisionId) {
                        $q->where('divisions.id_divisi', $divisionId);
                    });
                }
            })
            ->with(['points'])
            ->get();

        // Cari point absensi dengan config
        foreach ($kpis as $kpi) {
            foreach ($kpi->points as $point) {
                $isAbsensi = stripos($point->nama, 'absensi') !== false || 
                            stripos($point->nama, 'kehadiran') !== false;
                
                if ($isAbsensi && !empty($point->attendance_config)) {
                    $config = json_decode($point->attendance_config, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        Log::info("✅ USING ATTENDANCE CONFIG FROM KPI:", [
                            'point_id' => $point->id_point,
                            'point_name' => $point->nama,
                            'config' => $config
                        ]);
                        return $config;
                    }
                }
            }
        }

        // Fallback ke default
        Log::warning("❌ NO ATTENDANCE CONFIG FOUND, USING DEFAULT");
        return [
            'hadir_multiplier' => 3,
            'sakit_multiplier' => 0,
            'izin_multiplier' => 0,
            'mangkir_multiplier' => -3,
            'terlambat_multiplier' => -2,
            'workday_multiplier' => 2
        ];

    } catch (\Exception $e) {
        Log::error('Error getting attendance config: ' . $e->getMessage());
        return [
            'hadir_multiplier' => 3,
            'sakit_multiplier' => 0,
            'izin_multiplier' => 0,
            'mangkir_multiplier' => -3,
            'terlambat_multiplier' => -2,
            'workday_multiplier' => 2
        ];
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

        // ⚠️ PERBAIKAN: Hanya ambil periode AKTIF dengan absensi
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

        $allEmployeeData = [];

        foreach ($activePeriods as $period) {
            Log::info("Processing ACTIVE period: {$period->nama} (ID: {$period->id_periode})");

            // 1. Ambil semua Kepala Divisi yang AKTIF
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
                // 2. CEK APAKAH MEMILIKI ABSENSI DI PERIODE INI
                $hasAttendance = DB::table('attendances')
                    ->where('employee_id', $emp->id_karyawan)
                    ->where('periode_id', $period->id_periode)
                    ->exists();

                if (!$hasAttendance) {
                    continue; // Skip kalo ga ada absensi
                }

                // 3. ✅ CUMA TAMPILIN YANG BELUM DINILAI
                $hasKpiScore = DB::table('kpis_has_employees')
                    ->where('employees_id_karyawan', $emp->id_karyawan)
                    ->where('periode_id', $period->id_periode)
                    ->where('nilai_akhir', '>', 0)
                    ->exists();

                if ($hasKpiScore) {
                    continue; // Skip kalo udah dinilai
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
                $photoUrl = null;
                if ($emp->foto) {
                    if (filter_var($emp->foto, FILTER_VALIDATE_URL)) {
                        $photoUrl = $emp->foto;
                    } elseif (strpos($emp->foto, 'storage/') === 0) {
                        $photoUrl = asset($emp->foto);
                    } elseif (strpos($emp->foto, 'profile-photos/') === 0) {
                        $photoUrl = asset('storage/' . $emp->foto);
                    } else {
                        $photoUrl = asset('storage/' . $emp->foto);
                    }
                } else {
                    $photoUrl = asset('assets/images/profile_av.png');
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
                    'photo' => $photoUrl,
                    'division' => $division,
                    'division_id' => $divisionId,
                    'position' => $emp->position,
                    'period_id' => $period->id_periode
                ];

                $allEmployeeData[] = $employeeData;
                Log::info("✅ Added UNRATED employee in ACTIVE period:", [
                    'nama' => $emp->nama,
                    'period' => $period->nama
                ]);
            }
        }

        Log::info("Final UNRATED employees in ACTIVE periods:", [
            'total_unrated' => count($allEmployeeData),
            'active_periods_count' => $activePeriods->count()
        ]);

        return response()->json([
            'success' => true,
            'data' => $allEmployeeData,
            'debug_info' => [
                'total_active_periods' => $activePeriods->count(),
                'total_unrated_employees' => count($allEmployeeData),
                'note' => 'Hanya menampilkan karyawan yang BELUM dinilai di periode AKTIF'
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

            $employeeScores = DB::table('kpis_has_employees')
                ->where('periode_id', $periodId)
                ->join('employees', 'kpis_has_employees.employees_id_karyawan', '=', 'employees.id_karyawan')
                ->select(
                    'employees.id_karyawan',
                    'employees.nama',
                    'employees.status',
                    'employees.foto',
                    DB::raw('SUM(kpis_has_employees.nilai_akhir) as total_score')
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

                // ⚠️ PERBAIKAN: Ambil bulan dari tanggal_mulai periode
                $periodMonth = date('F', strtotime($period->tanggal_mulai));
                $periodYear = date('Y', strtotime($period->tanggal_mulai));

                $formattedData[] = [
                    'id_karyawan' => $emp->id_karyawan,
                    'nama' => $emp->nama,
                    'status' => $emp->status,
                    'score' => floatval($emp->total_score),
                    'period' => $period->nama,
                    'period_month' => $periodMonth,
                    'period_month_number' => date('n', strtotime($period->tanggal_mulai)),
                    'period_year' => $periodYear,
                    'photo' => $emp->foto ?? 'assets/images/profile_av.png',
                    'division' => $division,
                    'position' => $position
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

        // ⚠️ PERBAIKAN: AMBIL TOTAL SCORE DARI SUMBER YANG SAMA DENGAN LIST
        $totalScore = DB::table('kpis_has_employees')
            ->where('employees_id_karyawan', $employeeId)
            ->where('periode_id', $periodId)
            ->sum('nilai_akhir');

        // ⚠️ PERBAIKAN: Ambil detail KPI dengan nilai REAL dari database
        $kpiDetails = $this->getKpiDetailsFromDatabase($employeeId, $periodId);

        // Hitung ranking - SAMA DENGAN LIST
        $allEmployeeScores = DB::table('kpis_has_employees')
            ->where('periode_id', $periodId)
            ->select('employees_id_karyawan', DB::raw('SUM(nilai_akhir) as total_score'))
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
                    'total_score' => round($totalScore, 2), // ⚠️ PAKAI INI, SAMA DENGAN LIST
                    'average_score' => round($totalScore, 2), // Untuk konsistensi
                    'average_contribution' => round($totalScore, 2), // Untuk konsistensi
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

private function getKpiDetailsFromDatabase($employeeId, $periodId)
{
    $kpiData = [];
    
    // Ambil KPI berdasarkan divisi employee - LOGIC YANG SAMA DENGAN LIST
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
        $kpiBobot = floatval($kpi->bobot);
        
        // ⚠️ AMBIL NILAI AKHIR DARI DATABASE - SUMBER YANG SAMA DENGAN LIST
        $kpiFinalScore = DB::table('kpis_has_employees')
            ->where('kpis_id_kpi', $kpi->id_kpi)
            ->where('employees_id_karyawan', $employeeId)
            ->where('periode_id', $periodId)
            ->value('nilai_akhir');

        // Total Aspek Utama - PAKAI NILAI DATABASE YANG REAL
        $kpiData[] = [
            'aspek_kpi' => $aspekUtama,
            'sub_aspek_name' => 'TOTAL ASPEK',
            'score' => floatval($kpiFinalScore) ?? 0,
            'bobot' => $kpiBobot,
            'kontribusi' => floatval($kpiFinalScore) ?? 0,
            'is_total_aspek' => true
        ];

        // Untuk sub-aspek, ambil nilai REAL dari database
        $kpisHasEmployeeId = DB::table('kpis_has_employees')
            ->where('kpis_id_kpi', $kpi->id_kpi)
            ->where('employees_id_karyawan', $employeeId)
            ->where('periode_id', $periodId)
            ->value('id');

        foreach ($kpi->points as $point) {
            $pointScore = 0;
            $isAbsensi = stripos($point->nama, 'absensi') !== false 
                      || stripos($point->nama, 'kehadiran') !== false;

            // ⚠️ AMBIL NILAI REAL DARI DATABASE
            if ($isAbsensi && $kpisHasEmployeeId) {
                // Untuk absensi - ambil dari nilai_absensi
                $pointRecord = DB::table('kpi_points_has_employee')
                    ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                    ->where('kpi_point_id', $point->id_point)
                    ->first();
                $pointScore = $pointRecord->nilai_absensi ?? 0;
            } else {
                // Untuk non-absensi - hitung dari jawaban
                $pointTotal = 0;
                $answeredQuestions = 0;

                foreach ($point->questions as $question) {
                    $answer = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                        ->where('kpi_question_id_question', $question->id_question)
                        ->where('periode_id', $periodId)
                        ->first();

                    if ($answer && $answer->nilai !== null) {
                        // Konversi 1-4 ke 0-100 (SAMA DENGAN PERHITUNGAN ASLI)
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
                'sub_aspek_name' => $point->nama,
                'score' => $pointScore, // NILAI REAL
                'bobot' => $pointBobot,
                'kontribusi' => ($pointScore * $pointBobot) / 100, // Kontribusi
                'is_total_aspek' => false
            ];
        }
    }

    return $kpiData;
}

    public function getUnratedEmployees($divisionId)
    {
        try {
            \Log::info("getUnratedEmployees called", ['division_id' => $divisionId]);

            // ⚠️ PAKAI CARA YANG SAMA PERSIS DENGAN getAllEmployeeKpis
            $latestPeriod = Period::where('kpi_published', true)
                ->orderBy('tanggal_mulai', 'desc')
                ->first();

            if (!$latestPeriod) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Tidak ada periode dengan KPI yang dipublish'
                ]);
            }

            \Log::info("Using LATEST period for unrated check:", [
                'period_id' => $latestPeriod->id_periode,
                'period_name' => $latestPeriod->nama
            ]);

            // Ambil semua karyawan di divisi yang aktif
            $divisionEmployees = Employee::whereHas('roles', function($query) use ($divisionId) {
                $query->where('division_id', $divisionId);
            })
            ->where('status', 'Aktif')
            ->with(['roles.division'])
            ->get();

            $unratedEmployees = [];

            foreach ($divisionEmployees as $employee) {
                // ⚠️ CEK APAKAH SUDAH ADA NILAI DI PERIODE TERBARU
                $hasKpiScore = DB::table('kpis_has_employees')
                    ->where('employees_id_karyawan', $employee->id_karyawan)
                    ->where('periode_id', $latestPeriod->id_periode)
                    ->exists();

                if (!$hasKpiScore) {
                    $unratedEmployees[] = [
                        'id_karyawan' => $employee->id_karyawan,
                        'nama' => $employee->nama,
                        'foto' => $employee->foto,
                        'division' => $employee->roles->first()->division->nama_divisi ?? '-',
                        'position' => $employee->roles->first()->nama_jabatan ?? '-'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $unratedEmployees,
                'period' => $latestPeriod->nama,
                'period_id' => $latestPeriod->id_periode,
                'total_unrated' => count($unratedEmployees)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting unrated employees: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting unrated employees: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getLowPerformingEmployees($divisionId)
    {
        try {
            \Log::info("getLowPerformingEmployees called", ['division_id' => $divisionId]);

            // ⚠️ PAKAI CARA YANG SAMA PERSIS DENGAN getAllEmployeeKpis
            $latestPeriod = Period::where('kpi_published', true)
                ->orderBy('tanggal_mulai', 'desc')
                ->first();

            if (!$latestPeriod) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Tidak ada periode dengan KPI yang dipublish'
                ]);
            }

            // ⚠️ QUERY YANG SAMA DENGAN getAllEmployeeKpis + FILTER DIVISI + SCORE RENDAH
            $lowPerformers = DB::table('kpis_has_employees')
                ->where('periode_id', $latestPeriod->id_periode)
                ->join('employees', 'kpis_has_employees.employees_id_karyawan', '=', 'employees.id_karyawan')
                ->select(
                    'employees.id_karyawan',
                    'employees.nama',
                    'employees.foto',
                    'employees.no_telp as phone',
                    DB::raw('SUM(kpis_has_employees.nilai_akhir) as total_score')
                )
                ->where('employees.status', 'Aktif')
                ->groupBy('employees.id_karyawan', 'employees.nama', 'employees.foto', 'employees.no_telp')
                ->having('total_score', '<=', 50) // Nilai E
                ->orderBy('total_score', 'asc')
                ->get();

            // Filter berdasarkan divisi
            $filteredLowPerformers = [];
            foreach ($lowPerformers as $emp) {
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
                    $filteredLowPerformers[] = [
                        'id_karyawan' => $emp->id_karyawan,
                        'nama' => $emp->nama,
                        'foto' => $emp->foto,
                        'phone' => $emp->phone,
                        'position' => $employeeDetails->roles->first()->nama_jabatan ?? '-',
                        'score' => floatval($emp->total_score)
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $filteredLowPerformers,
                'period' => $latestPeriod->nama,
                'period_id' => $latestPeriod->id_periode
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

            // AMBIL PERIODE TERBARU YANG SUDAH DIPUBLISH (SAMA DENGAN getAllEmployeeKpis)
            $latestPeriod = Period::where('kpi_published', true)
                ->orderBy('tanggal_mulai', 'desc')
                ->first();

            if (!$latestPeriod) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Tidak ada periode dengan KPI yang dipublish'
                ]);
            }

            // QUERY YANG SAMA PERSIS DENGAN getAllEmployeeKpis TAPI FILTER SCORE RENDAH
            $lowPerformers = DB::table('kpis_has_employees')
                ->where('periode_id', $latestPeriod->id_periode)
                ->join('employees', 'kpis_has_employees.employees_id_karyawan', '=', 'employees.id_karyawan')
                ->select(
                    'employees.id_karyawan',
                    'employees.nama',
                    'employees.status',
                    'employees.foto',
                    'employees.no_telp as phone',
                    DB::raw('SUM(kpis_has_employees.nilai_akhir) as total_score')
                )
                ->where('employees.status', 'Aktif')
                ->groupBy('employees.id_karyawan', 'employees.nama', 'employees.status', 'employees.foto', 'employees.no_telp')
                ->having('total_score', '<=', 50) // FILTER: score <= 50
                ->orderBy('total_score', 'asc') // URUTKAN DARI TERENDAH
                ->limit(10) // MAKSIMAL 10 KARYAWAN
                ->get();

            \Log::info("Low performers from all divisions", [
                'period_id' => $latestPeriod->id_periode,
                'period_name' => $latestPeriod->nama,
                'count' => $lowPerformers->count()
            ]);

            $formattedData = [];

            foreach ($lowPerformers as $emp) {
                // Ambil detail divisi dan jabatan
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
                    'foto' => $emp->foto,
                    'phone' => $emp->phone,
                    'division' => $division,
                    'position' => $position,
                    'score' => floatval($emp->total_score)
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'period_info' => [ // TAMBAH INFO PERIODE UNTUK DEBUG
                    'id' => $latestPeriod->id_periode,
                    'nama' => $latestPeriod->nama,
                    'tanggal_mulai' => $latestPeriod->tanggal_mulai
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