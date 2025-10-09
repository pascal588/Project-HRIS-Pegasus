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

class KpiCalculationController extends Controller
{
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

        private function saveAttendanceScore($employeeId, $periodeId, $pointId, $finalScore)
    {
        try {
            Log::info("=== START SAVE ATTENDANCE SCORE ===", [
                'employee_id' => $employeeId,
                'periode_id' => $periodeId,
                'point_id' => $pointId,
                'final_score' => $finalScore
            ]);

            $point = KpiPoint::find($pointId);
            if (!$point) {
                Log::error("KPI Point not found: {$pointId}");
                return false;
            }

            $kpiId = $point->kpis_id_kpi;
            $originalBobot = $point->bobot; // Simpan bobot asli

            Log::info("Found KPI Point:", [
                'point_name' => $point->nama,
                'kpi_id' => $kpiId,
                'point_bobot' => $originalBobot
            ]);

            // Cari atau buat record kpis_has_employees
            $kpisHasEmployeeId = DB::table('kpis_has_employees')
                ->where('kpis_id_kpi', $kpiId)
                ->where('employees_id_karyawan', $employeeId)
                ->where('periode_id', $periodeId)
                ->value('id');

            if (!$kpisHasEmployeeId) {
                $kpisHasEmployeeId = DB::table('kpis_has_employees')->insertGetId([
                    'kpis_id_kpi' => $kpiId,
                    'employees_id_karyawan' => $employeeId,
                    'periode_id' => $periodeId,
                    'tahun' => date('Y'),
                    'bulan' => date('m'),
                    'nilai_akhir' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // ⚠️ PERBAIKAN: Simpan nilai absensi di kolom nilai_absensi, bobot tetap asli
            $existingRecord = DB::table('kpi_points_has_employee')
                ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                ->where('kpi_point_id', $pointId)
                ->first();

            if ($existingRecord) {
                $updated = DB::table('kpi_points_has_employee')
                    ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                    ->where('kpi_point_id', $pointId)
                    ->update([
                        'bobot' => $originalBobot, // ✅ Bobot asli
                        'nilai_absensi' => $finalScore, // ✅ Nilai absensi di kolom baru
                        'updated_at' => now(),
                    ]);

                Log::info("Updated kpi_points_has_employee:", [
                    'updated' => $updated,
                    'kpis_has_employee_id' => $kpisHasEmployeeId,
                    'point_id' => $pointId,
                    'bobot' => $originalBobot,
                    'nilai_absensi' => $finalScore
                ]);
            } else {
                $inserted = DB::table('kpi_points_has_employee')->insert([
                    'kpis_has_employee_id' => $kpisHasEmployeeId,
                    'kpi_point_id' => $pointId,
                    'bobot' => $originalBobot, // ✅ Bobot asli
                    'nilai_absensi' => $finalScore, // ✅ Nilai absensi di kolom baru
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info("Inserted kpi_points_has_employee:", [
                    'inserted' => $inserted,
                    'kpis_has_employee_id' => $kpisHasEmployeeId,
                    'point_id' => $pointId,
                    'bobot' => $originalBobot,
                    'nilai_absensi' => $finalScore
                ]);
            }

            // Hitung ulang nilai akhir KPI
            $this->calculateSingleKpiFinalScore($kpiId, $employeeId, $periodeId);

            Log::info("Attendance score saved in 'nilai_absensi' column, bobot preserved");

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to save attendance score: ' . $e->getMessage());
            return false;
        }
    }
    
private function calculateSingleKpiFinalScore($kpiId, $employeeId, $periodeId)
{
    try {
        $kpi = Kpi::with(['points.questions'])->find($kpiId);
        if (!$kpi) return 0;

        $totalAspekScore = 0;

        foreach ($kpi->points as $point) {
            $pointScore = 0;
            $isAbsensi = stripos($point->nama, 'absensi') !== false;

            if ($isAbsensi) {
                // Ambil nilai_absensi (0-100)
                $kpisHasEmployeeId = DB::table('kpis_has_employees')
                    ->where('kpis_id_kpi', $kpiId)
                    ->where('employees_id_karyawan', $employeeId)
                    ->where('periode_id', $periodeId)
                    ->value('id');

                if ($kpisHasEmployeeId) {
                    $pointRecord = DB::table('kpi_points_has_employee')
                        ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                        ->where('kpi_point_id', $point->id_point)
                        ->first();

                    $pointScore = $pointRecord->nilai_absensi ?? 0;
                }
            } else {
                // Untuk non-absensi - RUMUS BARU: (rata-rata sub aspek × 2.5) × bobot sub aspek
                $pointTotal = 0;
                $answeredQuestions = 0;

                foreach ($point->questions as $q) {
                    $score = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                        ->where('kpi_question_id_question', $q->id_question)
                        ->where('periode_id', $periodeId)
                        ->first();

                    if ($score && $score->nilai !== null) {
                        $pointTotal += $score->nilai; // Nilai 1-4
                        $answeredQuestions++;
                    }
                }

                // RUMUS BARU: (rata-rata × 2.5) × bobot
                $avgQuestionScore = $answeredQuestions > 0 ? ($pointTotal / $answeredQuestions) : 0;
                $pointScore = ($avgQuestionScore * 2.5) * (floatval($point->bobot) / 100);

                Log::info("Point calculation NEW FORMULA:", [
                    'point_name' => $point->nama,
                    'questions_answered' => $answeredQuestions,
                    'point_total' => $pointTotal,
                    'average_score' => $avgQuestionScore,
                    'point_bobot' => $point->bobot,
                    'point_score' => $pointScore,
                    'formula' => "({$avgQuestionScore} × 2.5) × ({$point->bobot} / 100) = {$pointScore}"
                ]);
            }

            $totalAspekScore += $pointScore;
        }

        // ⚠️ PERBAIKAN: Nilai akhir KPI = total kontribusi semua point (tidak dikali bobot aspek lagi)
        $finalAspekScore = $totalAspekScore;

        // Update database
        DB::table('kpis_has_employees')
            ->where('kpis_id_kpi', $kpiId)
            ->where('employees_id_karyawan', $employeeId)
            ->where('periode_id', $periodeId)
            ->update(['nilai_akhir' => $finalAspekScore]);

        Log::info("Final KPI Score for {$kpi->nama}:", [
            'total_contribution' => $finalAspekScore,
            'formula' => 'Σ((rata-rata sub aspek × 2.5) × bobot sub aspek)'
        ]);

        return $finalAspekScore;
    } catch (\Exception $e) {
        Log::error('Error calculating KPI score: ' . $e->getMessage());
        return 0;
    }
}

private function calculateAllFinalScores($employeeId, $periodeId)
{
    try {
        Log::info("=== CALCULATE ALL FINAL SCORES ===", [
            'employee_id' => $employeeId,
            'periode_id' => $periodeId
        ]);

        $employee = Employee::with(['roles.division'])->find($employeeId);
        $divisionId = null;

        if ($employee->roles && count($employee->roles) > 0) {
            $divisionId = $employee->roles[0]->division_id ?? null;
        }

        $kpis = Kpi::where('periode_id', $periodeId)
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
            Log::info("🔍 Calculating KPI: {$kpi->nama} (ID: {$kpi->id_kpi})");

            $totalAspekScore = 0;

            foreach ($kpi->points as $point) {
                $pointScore = 0;
                $isAbsensi = stripos($point->nama, 'absensi') !== false;

                if ($isAbsensi) {
                    // Ambil dari nilai_absensi (skala 0-100)
                    $kpisHasEmployeeId = DB::table('kpis_has_employees')
                        ->where('kpis_id_kpi', $kpi->id_kpi)
                        ->where('employees_id_karyawan', $employeeId)
                        ->where('periode_id', $periodeId)
                        ->value('id');

                    if ($kpisHasEmployeeId) {
                        $pointRecord = DB::table('kpi_points_has_employee')
                            ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                            ->where('kpi_point_id', $point->id_point)
                            ->first();

                        if ($pointRecord) {
                            // RUMUS: (nilai_absensi × bobot) / 100
                            $pointScore = ($pointRecord->nilai_absensi * floatval($point->bobot)) / 100;
                            Log::info("  ✅ Absensi score: {$pointRecord->nilai_absensi} × {$point->bobot}% = {$pointScore}");
                        }
                    }
                } else {
                    // Untuk non-absensi - RUMUS: (rata-rata × 2.5) × bobot
                    $pointTotal = 0;
                    $answeredQuestions = 0;

                    foreach ($point->questions as $q) {
                        $score = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                            ->where('kpi_question_id_question', $q->id_question)
                            ->where('periode_id', $periodeId)
                            ->first();

                        if ($score && $score->nilai !== null) {
                            $pointTotal += $score->nilai;
                            $answeredQuestions++;
                        }
                    }

                    if ($answeredQuestions > 0) {
                        $avgQuestionScore = $pointTotal / $answeredQuestions;
                        // RUMUS: (rata-rata × 2.5) × (bobot / 100)
                        $pointScore = ($avgQuestionScore * 2.5) * (floatval($point->bobot) / 100);
                        Log::info("  📈 Point score: {$avgQuestionScore} × 2.5 × {$point->bobot}% = {$pointScore}");
                    }
                }

                $totalAspekScore += $pointScore;
                Log::info("  🧮 Point contribution: {$pointScore}");
            }

            // Simpan nilai aspek utama (tanpa dikali bobot aspek lagi)
            $finalAspekScore = $totalAspekScore;
            $totalAllKpis += $finalAspekScore;

            Log::info("🎯 KPI SCORE for '{$kpi->nama}': {$finalAspekScore}");

            // Update atau create record di kpis_has_employees
            DB::table('kpis_has_employees')->updateOrInsert(
                [
                    'kpis_id_kpi' => $kpi->id_kpi,
                    'employees_id_karyawan' => $employeeId,
                    'periode_id' => $periodeId
                ],
                [
                    'nilai_akhir' => $finalAspekScore,
                    'tahun' => date('Y'),
                    'bulan' => date('m'),
                    'updated_at' => now()
                ]
            );
        }

        // ⚠️ RUMUS BARU: Total semua KPI × 10
        $finalTotalScore = $totalAllKpis * 10;
        
        Log::info("🎯 FINAL TOTAL SCORE: {$totalAllKpis} × 10 = {$finalTotalScore}");

        // ⚠️ SIMPAN TOTAL SCORE DI TABEL YANG SUDAH ADA (misalnya di tabel employees atau buat kolom baru)
        // Opsi 1: Simpan di kolom tambahan di tabel employees
        DB::table('employees')
            ->where('id_karyawan', $employeeId)
            ->update([
                'total_kpi_score' => $finalTotalScore,
                'updated_at' => now()
            ]);

        // Opsi 2: Atau buat kolom di tabel periods_has_employees jika ada
        // DB::table('periods_has_employees')->updateOrInsert(...)

        Log::info("✅ ALL FINAL SCORES CALCULATED WITH ×10 MULTIPLIER");
        return true;
    } catch (\Exception $e) {
        Log::error('❌ Error calculating final scores: ' . $e->getMessage());
        return false;
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

    // Private methods
    private function calculateAttendanceScore($employeeId, $periodeId, $pointBobot, $attendanceConfig = null)
    {
        try {
            // Ambil data absensi
            $attendances = Attendance::where('employee_id', $employeeId)
                ->where('periode_id', $periodeId)
                ->get();

            // Hitung berdasarkan jenis absensi
            $hadir = $attendances->where('status', 'Present at workday (PW)')->count();
            $sakit = $attendances->where('status', 'Sick (S)')->count();
            $izin = $attendances->where('status', 'Permission (I)')->count();
            $mangkir = $attendances->where('status', 'Absent (A)')->count();
            $terlambat = $attendances->where('late', '>', 0)->count();

            // Total hari kerja dalam periode
            $totalDays = $attendances->count();

            // ⚠️ RUMUS DINAMIS - DITERIMA DARI FRONTEND
            $config = $attendanceConfig ?? [
                'hadir_multiplier' => 3,
                'sakit_multiplier' => 0,
                'izin_multiplier' => 0,
                'mangkir_multiplier' => -3,
                'terlambat_multiplier' => -2,
                'workday_multiplier' => 2
            ];

            // Hitung total point (x)
            $totalPoints = ($hadir * $config['hadir_multiplier']) +
                ($sakit * $config['sakit_multiplier']) +
                ($izin * $config['izin_multiplier']) +
                ($mangkir * $config['mangkir_multiplier']) +
                ($terlambat * $config['terlambat_multiplier']);

            // Hitung total point maksimal (y)
            $maxPoints = $totalDays * $config['workday_multiplier'];

            // Hitung persentase kehadiran
            $attendancePercent = $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : 0;

            // ⚠️ KONVERSI KE SKALA 0-100 (sesuai rumus Excel)
            if ($attendancePercent >= 100) {
                $score = 10;
            } elseif ($attendancePercent >= 90) {
                $score = 8;
            } elseif ($attendancePercent >= 80) {
                $score = 6;
            } elseif ($attendancePercent >= 65) {
                $score = 4;
            } elseif ($attendancePercent >= 50) {
                $score = 2;
            } else {
                $score = 0;
            }

            return $score;
        } catch (\Exception $e) {
            Log::error('Error calculating attendance score: ' . $e->getMessage());
            return 0;
        }
    }


}
