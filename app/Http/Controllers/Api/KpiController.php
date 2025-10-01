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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class KpiController extends Controller
{
    // ================== KPI TEMPLATE ==================
    public function getAllKpis()
    {
        $kpis = Kpi::whereNull('periode_id')->with(['points.questions', 'divisions'])->get();
        return response()->json(['success' => true, 'data' => $kpis]);
    }

    public function storeKpi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_global' => 'required|boolean',
            'division_id' => 'nullable|integer|exists:divisions,id_divisi',
            'kpis' => 'required|array|min:1',
            'kpis.*.nama' => 'required|string|max:100',
            'kpis.*.bobot' => 'required|numeric|min:0|max:100',
            'kpis.*.is_global' => 'required|boolean',
            'kpis.*.points' => 'required|array|min:1',
            'kpis.*.points.*.nama' => 'required|string|max:255',
            'kpis.*.points.*.bobot' => 'required|numeric|min:0|max:100',
            // ⚠️ FIX: Questions tidak wajib (karena absensi boleh tanpa questions)
            'kpis.*.points.*.questions' => 'sometimes|array',
            'kpis.*.points.*.questions.*.pertanyaan' => 'required_with:kpis.*.points.*.questions|string|max:1000',
        ]);

        // Validasi tambahan: Jika is_global=false, division_id harus ada
        if (!$request->is_global && !$request->division_id) {
            return response()->json([
                'success' => false,
                'message' => 'Division ID is required for non-global KPI'
            ], 422);
        }

        // Validasi: Pastikan KPI global tidak memiliki division_id
        if ($request->is_global && $request->division_id) {
            return response()->json([
                'success' => false,
                'message' => 'Global KPI cannot have division_id'
            ], 422);
        }

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        // ⚠️ FIX: Validasi CUSTOM - Hanya sub-aspek absensi yang boleh tanpa pertanyaan
        $errors = [];
        foreach ($request->kpis as $kpiIndex => $kpiData) {
            foreach ($kpiData['points'] as $pointIndex => $pointData) {
                $pointName = $pointData['nama'] ?? '';
                $isAbsensi = stripos($pointName, 'absensi') !== false;
                $hasQuestions = !empty($pointData['questions']) && count($pointData['questions']) > 0;

                // ⚠️ VALIDASI PENTING: Jika bukan absensi dan tidak ada questions, ERROR
                if (!$isAbsensi && !$hasQuestions) {
                    $errors["kpis.{$kpiIndex}.points.{$pointIndex}.questions"] = [
                        "Sub-aspek '{$pointName}' harus memiliki minimal 1 pertanyaan"
                    ];
                }

                // ⚠️ VALIDASI: Jika absensi, tidak perlu ada questions (tapi boleh ada jika ingin)
                // Tidak ada validasi khusus untuk absensi tanpa questions

                // Validasi setiap pertanyaan (jika ada)
                if ($hasQuestions) {
                    foreach ($pointData['questions'] as $questionIndex => $questionData) {
                        if (empty($questionData['pertanyaan'] ?? '')) {
                            $errors["kpis.{$kpiIndex}.points.{$pointIndex}.questions.{$questionIndex}.pertanyaan"] = [
                                "Pertanyaan tidak boleh kosong"
                            ];
                        }
                    }
                }
            }
        }

        if (!empty($errors)) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $errors], 422);
        }

        DB::beginTransaction();
        try {
            $savedKpis = [];
            foreach ($request->kpis as $kpiData) {
                $kpiIsGlobal = $kpiData['is_global'] ?? $request->is_global;
                $kpi = $this->saveSingleKpi($kpiData, $kpiIsGlobal, $request->division_id, null);
                $savedKpis[] = $kpi;
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'KPI templates saved', 'data' => $savedKpis], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save KPI: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save KPI: ' . $e->getMessage()], 500);
        }
    }

    private function saveSingleKpi(array $kpiData, bool $isGlobal, $divisionId = null, $periodeId = null)
    {
        $kpi = isset($kpiData['id_kpi']) ? Kpi::findOrFail($kpiData['id_kpi']) : new Kpi();
        $kpi->nama = $kpiData['nama'];
        $kpi->bobot = $kpiData['bobot'];

        // ⚠️ FIX: Jangan ubah is_global untuk KPI yang sudah ada
        if (!$kpi->exists) {
            $kpi->is_global = $isGlobal;
        }

        $kpi->periode_id = $periodeId;
        $kpi->save();

        if (isset($kpiData['points'])) {
        $existingPointIds = [];
        foreach ($kpiData['points'] as $pointData) {
            $point = isset($pointData['id_point']) ? KpiPoint::findOrFail($pointData['id_point']) : new KpiPoint();
            $point->kpis_id_kpi = $kpi->id_kpi;
            $point->nama = $pointData['nama'];
            $point->bobot = $pointData['bobot'];
            $point->save();
            $existingPointIds[] = $point->id_point;

            // ⚠️ FIX: Deteksi absensi dari NAMA (tanpa simpan di database)
            $isAbsensi = stripos($pointData['nama'], 'absensi') !== false || 
                        stripos($pointData['nama'], 'kehadiran') !== false;

            if (isset($pointData['questions']) && count($pointData['questions']) > 0) {
                $existingQuestionIds = [];
                foreach ($pointData['questions'] as $qData) {
                    $question = isset($qData['id_question']) ? KpiQuestion::findOrFail($qData['id_question']) : new KpiQuestion();
                    $question->kpi_point_id = $point->id_point;
                    $question->pertanyaan = $qData['pertanyaan'];
                    $question->save();
                    $existingQuestionIds[] = $question->id_question;
                }
                KpiQuestion::where('kpi_point_id', $point->id_point)
                    ->whereNotIn('id_question', $existingQuestionIds)
                    ->delete();
            } else {
                // ⚠️ FIX: Untuk absensi, hapus questions yang ada
                if ($isAbsensi) {
                    KpiQuestion::where('kpi_point_id', $point->id_point)->delete();
                } else {
                        // ⚠️ Untuk absensi, boleh tanpa questions - hapus semua questions yang ada
                        KpiQuestion::where('kpi_point_id', $point->id_point)->delete();
                    }
                }
            }
            KpiPoint::where('kpis_id_kpi', $kpi->id_kpi)->whereNotIn('id_point', $existingPointIds)->delete();
        }

        if ($isGlobal) {
            $allDivisions = Division::pluck('id_divisi');
            foreach ($allDivisions as $divId) {
                DB::table('division_has_kpis')->updateOrInsert(
                    ['id_divisi' => $divId, 'kpis_id_kpi' => $kpi->id_kpi],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        } elseif ($divisionId) {
            DB::table('division_has_kpis')->updateOrInsert(
                ['id_divisi' => $divisionId, 'kpis_id_kpi' => $kpi->id_kpi],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        return $kpi->load('points.questions');
    }

    public function getKpiTemplates(Request $request)
    {
        $query = Kpi::whereNull('periode_id');
        if ($request->has('is_global')) $query->where('is_global', $request->is_global);
        if ($request->has('division_id')) {
            $query->whereHas('divisions', function ($q) use ($request) {
                $q->where('divisions.id_divisi', $request->division_id);
            });
        }
        $kpis = $query->with(['points.questions', 'divisions'])->get();
        return response()->json(['success' => true, 'data' => $kpis]);
    }

    // ================== COPY KPI TO PERIOD ==================
    public function copyTemplatesToPeriod($periodId)
    {
        $period = Period::findOrFail($periodId);
        DB::beginTransaction();
        try {
            $copiedCount = 0;
            $templates = Kpi::whereNull('periode_id')->with('points.questions', 'divisions')->get();
            foreach ($templates as $template) {
                $this->copyKpiToPeriod($template, $periodId);
                $copiedCount++;
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Copied to period', 'copied_count' => $copiedCount]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to copy KPI: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed copy: ' . $e->getMessage()], 500);
        }
    }

    private function copyKpiToPeriod(Kpi $template, $periodId)
    {
        $newKpi = $template->replicate();
        $newKpi->periode_id = $periodId;
        $newKpi->save();

        foreach ($template->points as $point) {
            $newPoint = $point->replicate();
            $newPoint->kpis_id_kpi = $newKpi->id_kpi;
            $newPoint->save();

            foreach ($point->questions as $question) {
                $newQuestion = $question->replicate();
                $newQuestion->kpi_point_id = $newPoint->id_point;
                $newQuestion->save();
            }
        }

        foreach ($template->divisions as $division) {
            DB::table('division_has_kpis')->insert([
                'id_divisi' => $division->id_divisi,
                'kpis_id_kpi' => $newKpi->id_kpi,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        return $newKpi;
    }

    // ================== KPI BY PERIOD ==================
    public function getKpisByPeriod($periodId)
    {
        $period = Period::findOrFail($periodId);
        $kpis = Kpi::where('periode_id', $periodId)->with(['points.questions', 'divisions'])->get();
        return response()->json(['success' => true, 'data' => ['period' => $period, 'kpis' => $kpis]]);
    }
    // checkpoint
   public function listKpiByDivision($divisionId, Request $request)
{
    $periodeId = $request->get('periode_id');

    Log::info("listKpiByDivision Debug:", [
        'division_id' => $divisionId,
        'periode_id' => $periodeId
    ]);

    if ($periodeId) {
        // ⚠️ PERBAIKAN KRITIS: Query yang lebih spesifik
        $globalKpis = Kpi::where('periode_id', $periodeId)
            ->where('is_global', true)
            ->with('points.questions')
            ->get();

        $divisionKpis = Kpi::where('periode_id', $periodeId)
            ->whereHas('divisions', function ($q) use ($divisionId) {
                $q->where('divisions.id_divisi', $divisionId);
            })
            ->with('points.questions')
            ->get();

        // ⚠️ PERBAIKAN: Gabungkan dan hapus duplikasi berdasarkan ID KPI
        $allKpis = $globalKpis->merge($divisionKpis);
        
        // Hapus duplikasi berdasarkan id_kpi
        $uniqueKpis = $allKpis->unique('id_kpi')->values();

        Log::info("KPI Deduplication Results:", [
            'global_count' => $globalKpis->count(),
            'division_count' => $divisionKpis->count(),
            'before_dedup' => $allKpis->count(),
            'after_dedup' => $uniqueKpis->count(),
            'duplicates_removed' => $allKpis->count() - $uniqueKpis->count()
        ]);

        $kpis = $uniqueKpis;

    } else {
        // Template KPI (tanpa periode)
        $globalKpis = Kpi::where('is_global', true)
            ->whereNull('periode_id')
            ->with('points.questions')
            ->get();

        $divisionKpis = Kpi::whereHas('divisions', function ($q) use ($divisionId) {
                $q->where('divisions.id_divisi', $divisionId);
            })
            ->whereNull('periode_id')
            ->with('points.questions')
            ->get();

        // Hapus duplikasi
        $kpis = $globalKpis->merge($divisionKpis)
            ->unique('id_kpi')
            ->values();
    }

    return response()->json([
        'success' => true, 
        'data' => $kpis,
        'debug' => [
            'division_id' => $divisionId,
            'periode_id' => $periodeId,
            'total_kpis' => $kpis->count()
        ]
    ]);
}
    public function listGlobalKpi()
    {
        $kpis = Kpi::where('is_global', true)->whereNull('periode_id')->with('points.questions')->get();
        return response()->json(['success' => true, 'data' => $kpis]);
    }

    // ================== CRUD ==================
    public function updateKpi(Request $request, $id)
    {
        $kpi = Kpi::findOrFail($id);
        $kpi->update($request->only(['nama', 'bobot', 'is_global']));
        return response()->json(['success' => true, 'message' => 'KPI updated', 'data' => $kpi]);
    }

    public function deleteKpi($id)
    {
        $kpi = Kpi::findOrFail($id);
        $kpi->delete();
        return response()->json(['success' => true, 'message' => 'KPI deleted']);
    }

    // ================== DELETE KPI POINT ==================
    // ================== DELETE KPI POINT ==================
    public function deleteKpiPoint($id)
    {
        DB::beginTransaction();
        try {
            $point = KpiPoint::findOrFail($id);

            // Log untuk debugging
            Log::info("Deleting KPI Point ID: {$id}");

            // Hapus semua questions terkait terlebih dahulu
            $questionsCount = KpiQuestion::where('kpi_point_id', $id)->delete();
            Log::info("Deleted {$questionsCount} questions for point ID: {$id}");

            // Hapus point
            $point->delete();

            DB::commit();

            Log::info("Successfully deleted KPI point ID: {$id}");

            return response()->json([
                'success' => true,
                'message' => 'Sub-aspek berhasil dihapus'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error("KPI Point not found: {$id}");

            return response()->json([
                'success' => false,
                'message' => 'Sub-aspek tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete KPI point: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus sub-aspek: ' . $e->getMessage()
            ], 500);
        }
    }

    // ================== DELETE KPI QUESTION ==================
    public function deleteKpiQuestion($id)
    {
        try {
            $question = KpiQuestion::findOrFail($id);
            $question->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pertanyaan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete KPI question: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pertanyaan: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==================== ATTENDANCE SCORE CALCULATION ==================
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
        $totalBobotPoint = 0;

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
                // Untuk non-absensi
                $pointTotal = 0;
                $answeredQuestions = 0;

                foreach ($point->questions as $q) {
                    $score = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                        ->where('kpi_question_id_question', $q->id_question)
                        ->where('periode_id', $periodeId)
                        ->first();

                    if ($score && $score->nilai !== null) {
                        // Konversi 1-4 ke 0-100
                        $questionScore = (($score->nilai - 1) / 3) * 100;
                        $pointTotal += $questionScore;
                        $answeredQuestions++;
                    }
                }

                $pointScore = $answeredQuestions > 0 ? ($pointTotal / $answeredQuestions) : 0;
            }

            $pointBobot = floatval($point->bobot) ?? 0;
            
            // ⚠️⚠️⚠️ PERBAIKAN PERKALIAN YANG BENAR:
            // Kontribusi = (Nilai Point × Bobot Point) / 100
            $pointContribution = ($pointScore * $pointBobot) / 100;
            
            $totalAspekScore += $pointContribution;
            $totalBobotPoint += $pointBobot;

            Log::info("Point calculation FIXED:", [
                'point_name' => $point->nama,
                'point_score' => $pointScore,
                'point_bobot' => $pointBobot,
                'contribution' => $pointContribution, // Harusnya: (73.33 × 30) / 100 = 22.00
                'formula' => "({$pointScore} × {$pointBobot}) / 100 = {$pointContribution}"
            ]);
        }

        // ⚠️ PERBAIKAN: Nilai akhir KPI = total kontribusi semua point
        $finalAspekScore = $totalAspekScore;

        DB::table('kpis_has_employees')
            ->where('kpis_id_kpi', $kpiId)
            ->where('employees_id_karyawan', $employeeId)
            ->where('periode_id', $periodeId)
            ->update(['nilai_akhir' => $finalAspekScore]);

        return $finalAspekScore;

    } catch (\Exception $e) {
        Log::error('Error calculating KPI score: ' . $e->getMessage());
        return 0;
    }
}

    public function storeEmployeeScore(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id_karyawan' => 'required|exists:employees,id_karyawan',
        'periode_id' => 'required|exists:periods,id_periode',
        'hasil' => 'required|array|min:1',
        'hasil.*.id_aspek' => 'required|exists:kpis,id_kpi',
        'hasil.*.jawaban' => 'required|array|min:1',
        'hasil.*.jawaban.*.id' => 'required|exists:kpi_questions,id_question',
        'hasil.*.jawaban.*.jawaban' => 'required|integer|min:1|max:4',
        // ✅ TAMBAH PARAMETER ABSENSI
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

    $period = Period::findOrFail($request->periode_id);
    if ($period->status === 'locked') {
        return response()->json([
            'success' => false,
            'message' => 'Tidak dapat menyimpan nilai: Periode sudah dikunci'
        ], 400);
    }

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

        Log::info("Employee division:", ['division_id' => $divisionId]);

        $kpis = Kpi::where('periode_id', $periodeId)
            ->where(function($query) use ($divisionId) {
                $query->where('is_global', true);
                
                if ($divisionId) {
                    $query->orWhereHas('divisions', function($q) use ($divisionId) {
                        $q->where('divisions.id_divisi', $divisionId);
                    });
                }
            })
            ->with(['points.questions'])
            ->get();

        Log::info("KPI to calculate:", [
            'total_kpis' => $kpis->count(),
            'kpi_names' => $kpis->pluck('nama')
        ]);

        foreach ($kpis as $kpi) {
            Log::info("🔍 Calculating KPI: {$kpi->nama} (ID: {$kpi->id_kpi})");
            
            $totalAspekScore = 0;
            $totalBobotPoint = 0;

            foreach ($kpi->points as $point) {
                $pointScore = 0;
                $isAbsensi = stripos($point->nama, 'absensi') !== false;

                Log::info("  📊 Point: {$point->nama} (Absensi: {$isAbsensi})");

                if ($isAbsensi) {
                    // Ambil dari nilai_absensi (skala 0-100)
                    $kpisHasEmployeeId = DB::table('kpis_has_employees')
                        ->where('kpis_id_kpi', $kpi->id_kpi)
                        ->where('employees_id_karyawan', $employeeId)
                        ->where('periode_id', $periodeId)
                        ->value('id');

                    Log::info("  📍 KPI Has Employee ID: {$kpisHasEmployeeId}");

                    if ($kpisHasEmployeeId) {
                        $pointRecord = DB::table('kpi_points_has_employee')
                            ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                            ->where('kpi_point_id', $point->id_point)
                            ->first();

                        if ($pointRecord) {
                            $pointScore = ($pointRecord->nilai_absensi ?? 0) / 10; // Konversi ke 0-10
                            Log::info("  ✅ Absensi score from DB: {$pointRecord->nilai_absensi} → {$pointScore}/10");
                        } else {
                            Log::warning("  ❌ No absensi record found for point: {$point->id_point}");
                        }
                    }
                } else {
                    // Untuk non-absensi, hitung dari jawaban questions
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
                            Log::info("  ✅ Question answered: {$q->id_question} = {$score->nilai}");
                        } else {
                            Log::warning("  ❌ Question not answered: {$q->id_question}");
                        }
                    }

                    if ($answeredQuestions > 0) {
                        $avgQuestionScore = $pointTotal / $answeredQuestions;
                        $pointScore = $avgQuestionScore * 2.5; // Konversi ke 0-10
                        Log::info("  📈 Point score calculated: {$pointTotal}/{$answeredQuestions} = {$avgQuestionScore} → {$pointScore}/10");
                    } else {
                        Log::warning("  ❌ No questions answered for point: {$point->nama}");
                    }
                }

                $pointBobot = floatval($point->bobot) ?? 0;
                $pointContribution = ($pointScore * $pointBobot) / 100;
                $totalAspekScore += $pointContribution;
                $totalBobotPoint += $pointBobot;

                Log::info("  🧮 Point contribution: {$pointScore} × {$pointBobot}% = {$pointContribution}");
            }

            // ⚠️ PERBAIKAN: Kalikan dengan 10 untuk konversi ke skala 0-100
            $finalAspekScore = $totalBobotPoint > 0 ? ($totalAspekScore * 10) : 0;

            Log::info("🎯 FINAL KPI SCORE for '{$kpi->nama}': {$totalAspekScore} × 10 = {$finalAspekScore}");

            // Update atau create record di kpis_has_employees
            $existingRecord = DB::table('kpis_has_employees')
                ->where('kpis_id_kpi', $kpi->id_kpi)
                ->where('employees_id_karyawan', $employeeId)
                ->where('periode_id', $periodeId)
                ->first();

            if ($existingRecord) {
                DB::table('kpis_has_employees')
                    ->where('kpis_id_kpi', $kpi->id_kpi)
                    ->where('employees_id_karyawan', $employeeId)
                    ->where('periode_id', $periodeId)
                    ->update([
                        'nilai_akhir' => $finalAspekScore, // Sekarang dalam skala 0-100
                        'updated_at' => now()
                    ]);
                Log::info("  ✅ Updated existing record");
            } else {
                DB::table('kpis_has_employees')->insert([
                    'kpis_id_kpi' => $kpi->id_kpi,
                    'employees_id_karyawan' => $employeeId,
                    'periode_id' => $periodeId,
                    'tahun' => date('Y'),
                    'bulan' => date('m'),
                    'nilai_akhir' => $finalAspekScore, // Sekarang dalam skala 0-100
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Log::info("  ✅ Created new record");
            }
        }

        Log::info("✅ ALL FINAL SCORES CALCULATED");
        return true;

    } catch (\Exception $e) {
        Log::error('❌ Error calculating final scores: ' . $e->getMessage());
        return false;
    }
}
    // Tambahkan method untuk mendapatkan status penilaian
public function getEmployeeKpiStatus($employeeId, $periodId)
{
    try {
        $employee = Employee::with(['roles.division'])->findOrFail($employeeId);
        
        // Ambil KPI untuk employee di periode tertentu
        $kpis = Kpi::where('periode_id', $periodId)
            ->where(function($query) use ($employee) {
                $divisionId = null;
                if ($employee->roles && count($employee->roles) > 0) {
                    $divisionId = $employee->roles[0]->division_id ?? null;
                }

                $query->where('is_global', true);
                
                if ($divisionId) {
                    $query->orWhereHas('divisions', function($q) use ($divisionId) {
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

    // ================== ATTENDANCE SUMMARY ==================
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
// ==================== GET ATTENDANCE CONFIG ====================
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

// ==================== GET ATTENDANCE CALCULATION DATA ====================
public function getAttendanceCalculationData($employeeId, $periodeId)
{
    try {
        $employee = Employee::findOrFail($employeeId);
        $period = Period::findOrFail($periodeId);

        // ⚠️ PERBAIKAN: Ambil hanya hari kerja (bukan libur)
        $attendances = Attendance::where('employee_id', $employeeId)
            ->where('periode_id', $periodeId)
            ->get();

        // ⚠️ PERBAIKAN: Filter hanya hari kerja (bukan NW - Non-working day)
        $workDays = $attendances->where('status', '!=', 'Non-working day (NW)');
        $totalWorkDays = $workDays->count();

        // Hitung summary HANYA dari hari kerja
        $hadir = $workDays->where('status', 'Present at workday (PW)')->count();
        $sakit = $workDays->where('status', 'Sick (S)')->count();
        $izin = $workDays->where('status', 'Permission (I)')->count();
        $mangkir = $workDays->where('status', 'Absent (A)')->count();
        $terlambat = $workDays->where('late', '>', 0)->count();

        // ⚠️ PERBAIKAN: Juga hitung hari libur untuk info
        $libur = $attendances->where('status', 'Non-working day (NW)')->count();

        // Konfigurasi default
        $config = [
            'hadir_multiplier' => 3,
            'sakit_multiplier' => 0,
            'izin_multiplier' => 0,
            'mangkir_multiplier' => -3,
            'terlambat_multiplier' => -2,
            'workday_multiplier' => 2
        ];

        // Hitung berdasarkan rumus Excel
        $kehadiranPoints = $hadir * $config['hadir_multiplier'];
        $sakitPoints = $sakit * $config['sakit_multiplier'];
        $izinPoints = $izin * $config['izin_multiplier'];
        $mangkirPoints = $mangkir * $config['mangkir_multiplier'];
        $subTotal = $kehadiranPoints + $sakitPoints + $izinPoints + $mangkirPoints;
        
        $terlambatPoints = $terlambat * $config['terlambat_multiplier'];
        $totalPointsX = $subTotal + $terlambatPoints;
        
        // ⚠️ PERBAIKAN: y = total hari kerja × multiplier
        $maxPointsY = $totalWorkDays * $config['workday_multiplier'];
        
        $attendancePercent = $maxPointsY > 0 ? ($totalPointsX / $maxPointsY) * 100 : 0;

        // ⚠️ PERBAIKAN: Konversi ke skala 0-100 yang benar
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
                    'total_days' => $attendances->count(), // total semua hari
                    'total_work_days' => $totalWorkDays,   // ⚠️ HARI KERJA SAJA
                    'libur' => $libur
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
                'config' => $config,
                'score_conversion' => [
                    ['min' => 100, 'max' => 100, 'score' => 100],
                    ['min' => 90, 'max' => 99, 'score' => 80],
                    ['min' => 80, 'max' => 89, 'score' => 60],
                    ['min' => 65, 'max' => 79, 'score' => 40],
                    ['min' => 50, 'max' => 64, 'score' => 20],
                    ['min' => 0, 'max' => 49, 'score' => 0]
                ]
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error calculating attendance data: ' . $e->getMessage()
        ], 500);
    }
}

    // ================== GET SCORE PER ASPEK UTAMA ==================
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

public function getEmployeeKpiForPeriod($employeeId, $periodId)
{
    // Dapatkan data karyawan dan divisinya
    $employee = Employee::with(['roles.division'])->findOrFail($employeeId);
    
    // Ambil divisi karyawan (ambil divisi pertama)
    $divisionId = null;
    if ($employee->roles && count($employee->roles) > 0) {
        $divisionId = $employee->roles[0]->division_id ?? null;
    }

    // ⚠️ PERBAIKAN: Debug data employee dan division
    Log::info("Employee KPI Query Debug:", [
        'employee_id' => $employeeId,
        'period_id' => $periodId,
        'division_id' => $divisionId,
        'employee_roles' => $employee->roles->pluck('division_id')
    ]);

    // Di method getEmployeeKpiForPeriod - GANTI query menjadi:
    $kpis = Kpi::where('periode_id', $periodId)
        ->where(function($query) use ($divisionId) {
            // Pertama coba ambil KPI divisi spesifik
            if ($divisionId) {
                $query->whereHas('divisions', function($q) use ($divisionId) {
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

    // ⚠️ PERBAIKAN: Debug hasil query
    Log::info("KPI Query Results:", [
        'total_kpis_found' => $kpis->count(),
        'kpi_details' => $kpis->map(function($kpi) {
            return [
                'id' => $kpi->id_kpi,
                'nama' => $kpi->nama,
                'is_global' => $kpi->is_global,
                'division_count' => $kpi->divisions->count()
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

        foreach ($kpi->points as $point) {
            $questionsData = [];
            $isAbsensi = stripos($point->nama, 'absensi') !== false;

            // ⚠️ PERBAIKAN: Untuk absensi, ambil nilai dari kolom `nilai_absensi`
            $pointScore = 0;
            if ($isAbsensi && $kpisHasEmployeeId) {
                $pointRecord = DB::table('kpi_points_has_employee')
                    ->where('kpis_has_employee_id', $kpisHasEmployeeId)
                    ->where('kpi_point_id', $point->id_point)
                    ->first();
                
                // ⚠️ PERBAIKAN: Ambil dari nilai_absensi
                $pointScore = $pointRecord->nilai_absensi ?? 0;
            }

            foreach ($point->questions as $q) {
                $answer = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                    ->where('kpi_question_id_question', $q->id_question)
                    ->where('periode_id', $periodId)
                    ->first();

                $questionsData[] = [
                    'id' => $q->id_question,
                    'pertanyaan' => $q->pertanyaan,
                    'answer' => $answer ? (int)$answer->nilai : null
                ];
            }

            $pointsData[] = [
                'id' => $point->id_point,
                'nama' => $point->nama,
                'bobot' => (float)$point->bobot,
                'is_absensi' => $isAbsensi,
                'point_score' => (float)$pointScore,
                'questions' => $questionsData
            ];
        }

        $data[] = [
            'id' => $kpi->id_kpi,
            'aspek' => $kpi->nama,
            'nama' => $kpi->nama,
            'bobot' => (float)$kpi->bobot,
            'is_global' => $kpi->is_global,
            'points' => $pointsData
        ];
    }

    // ⚠️ PERBAIKAN: Debug final data
    Log::info("Final KPI Data for Employee:", [
        'employee_id' => $employeeId,
        'total_kpis_returned' => count($data),
        'kpis' => array_map(function($item) {
            return [
                'id' => $item['id'],
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
        'debug_info' => [ // ⚠️ TAMBAH DEBUG INFO
            'division_id' => $divisionId,
            'total_kpis' => count($data),
            'kpi_list' => array_column($data, 'nama')
        ]
    ]);
}

    // ================== GET TOTAL WEIGHT BY DIVISION ==================
    public function getTotalWeightByDivision($divisionId)
    {
        // Get global KPI total weight
        $globalWeight = Kpi::where('is_global', true)
            ->whereNull('periode_id')
            ->sum('bobot');

        // Get division-specific KPI total weight
        $divisionWeight = Kpi::whereHas('divisions', function ($q) use ($divisionId) {
            $q->where('divisions.id_divisi', $divisionId);
        })
            ->whereNull('periode_id')
            ->sum('bobot');

        $totalWeight = $globalWeight + $divisionWeight;

        return response()->json([
            'success' => true,
            'data' => [
                'global_weight' => $globalWeight,
                'division_weight' => $divisionWeight,
                'total_weight' => $totalWeight
            ]
        ]);
    }

    public function publishToPeriod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period_id' => 'required|exists:periods,id_periode',
            'deadline_days' => 'required|integer|min:1|max:60', // Deadline dalam hari
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $period = Period::findOrFail($request->period_id);

            // Validasi: periode harus memiliki absensi yang sudah diupload
            if (!$period->attendance_uploaded) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa mempublish KPI: Absensi untuk periode ini belum diupload'
                ], 400);
            }

            // Validasi: periode tidak boleh sudah locked
            if ($period->status === 'locked') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa mempublish KPI: Periode sudah dikunci'
                ], 400);
            }

            // Hitung tanggal evaluasi berdasarkan deadline
            $evaluationStartDate = now();
            $evaluationEndDate = now()->addDays($request->deadline_days);
            $editingEndDate = $evaluationEndDate->copy()->addDays(3); // Tambah 3 hari untuk editing

            // Update periode dengan tanggal evaluasi
            $period->update([
                'evaluation_start_date' => $evaluationStartDate,
                'evaluation_end_date' => $evaluationEndDate,
                'editing_start_date' => $evaluationEndDate->addDay(), // Editing mulai setelah evaluasi
                'editing_end_date' => $editingEndDate,
                'status' => 'active',
                'kpi_published' => true,
                'kpi_published_at' => now()
            ]);

            // Copy template KPI ke periode
            $copiedCount = 0;
            $templates = Kpi::whereNull('periode_id')->with('points.questions', 'divisions')->get();

            foreach ($templates as $template) {
                // Cek apakah KPI sudah ada di periode ini
                $existingKpi = Kpi::where('periode_id', $period->id_periode)
                    ->where('nama', $template->nama)
                    ->where('is_global', $template->is_global)
                    ->first();

                if (!$existingKpi) {
                    $this->copyKpiToPeriod($template, $period->id_periode);
                    $copiedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'KPI berhasil dipublish ke periode ' . $period->nama,
                'data' => [
                    'period' => $period,
                    'copied_kpi_count' => $copiedCount,
                    'evaluation_period' => [
                        'start' => $evaluationStartDate->format('d M Y'),
                        'end' => $evaluationEndDate->format('d M Y'),
                        'deadline_days' => $request->deadline_days
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to publish KPI: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mempublish KPI: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAvailablePeriodsForPublishing()
    {
        $periods = Period::where('attendance_uploaded', true)
            ->where('status', '!=', 'locked')
            ->where(function ($query) {
                $query->where('kpi_published', false)
                    ->orWhereNull('kpi_published');
            })
            ->orderBy('tanggal_mulai', 'desc')
            ->get(['id_periode', 'nama', 'tanggal_mulai', 'tanggal_selesai', 'status', 'attendance_uploaded']);

        return response()->json([
            'success' => true,
            'data' => $periods
        ]);
    }

public function getAllEmployeeKpis(Request $request)
{
    try {
        $activePeriod = Period::where('status', 'active')
            ->where('kpi_published', true)
            ->first();

        if (!$activePeriod) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Tidak ada periode aktif dengan KPI yang dipublish'
            ]);
        }

        $employees = Employee::with(['roles.division'])
            ->where('status', 'Aktif')
            ->get();

        $formattedData = [];

        foreach ($employees as $employee) {
            $divisionId = null;
            if ($employee->roles && count($employee->roles) > 0) {
                $divisionId = $employee->roles[0]->division_id ?? null;
            }

            // ⚠️ PERBAIKAN: Gunakan query dengan deduplikasi
            $kpis = Kpi::where('periode_id', $activePeriod->id_periode)
                ->where(function($query) use ($divisionId) {
                    $query->where('is_global', true);
                    
                    if ($divisionId) {
                        $query->orWhereHas('divisions', function($q) use ($divisionId) {
                            $q->where('divisions.id_divisi', $divisionId);
                        });
                    }
                })
                ->get()
                ->unique('id_kpi') // ⚠️ DEDUPLIKASI
                ->values();

            $totalScore = 0;

            foreach ($kpis as $kpi) {
                $kpiScore = DB::table('kpis_has_employees')
                    ->where('kpis_id_kpi', $kpi->id_kpi)
                    ->where('employees_id_karyawan', $employee->id_karyawan)
                    ->where('periode_id', $activePeriod->id_periode)
                    ->value('nilai_akhir');

                $kpiScore = floatval($kpiScore) ?? 0;
                $totalScore += $kpiScore;
            }

            $roles = $employee->roles;
            $divisionName = $roles->first()->division->nama_divisi ?? '-';
            $positionNames = $roles->pluck('nama_jabatan')->unique()->implode(', ') ?: '-';

            if ($totalScore > 0) {
                $formattedData[] = [
                    'id_karyawan' => $employee->id_karyawan,
                    'nama' => $employee->nama,
                    'status' => $employee->status,
                    'score' => $totalScore,
                    'period' => $activePeriod->nama,
                    'period_month' => date('F', strtotime($activePeriod->tanggal_mulai)),
                    'period_year' => date('Y', strtotime($activePeriod->tanggal_mulai)),
                    'photo' => $employee->foto ?? 'assets/images/profile_av.png',
                    'division' => $divisionName,
                    'position' => $positionNames,
                    'kpi_details' => $this->getEmployeeKpiDetails($employee->id_karyawan, $activePeriod->id_periode)
                ];
            }
        }

        usort($formattedData, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedData
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to fetch KPI data: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch KPI data: ' . $e->getMessage()
        ], 500);
    }
}

// ⚠️ PERBAIKI juga method getEmployeeKpiDetails
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
            ->where(function($query) use ($divisionId) {
                $query->where('is_global', true);
                
                if ($divisionId) {
                    $query->orWhereHas('divisions', function($q) use ($divisionId) {
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

// ================== GET EMPLOYEE KPI DETAIL ==================
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

        $kpiResponse = $this->getEmployeeKpiForPeriod($employeeId, $periodId);
        $kpiData = json_decode($kpiResponse->getContent(), true);

        if (!$kpiData['success']) {
            throw new \Exception($kpiData['message'] ?? 'Failed to get KPI data');
        }

        $totalScore = 0;
        $totalBobot = 0;
        $kpiDetails = [];

        foreach ($kpiData['data'] as $kpi) {
            $kpiScore = 0;
            $kpiBobot = floatval($kpi['bobot']);
            
            foreach ($kpi['points'] as $point) {
                $pointBobot = floatval($point['bobot']);
                
                if ($point['is_absensi']) {
                    $pointScore = $point['point_score']; // Nilai 0-100
                } else {
                    $pointTotal = 0;
                    $answeredQuestions = 0;
                    
                    foreach ($point['questions'] as $question) {
                        if ($question['answer'] !== null) {
                            $questionScore = (($question['answer'] - 1) / 3) * 100;
                            $pointTotal += $questionScore;
                            $answeredQuestions++;
                        }
                    }
                    
                    if ($answeredQuestions > 0) {
                        $pointScore = $pointTotal / $answeredQuestions;
                    } else {
                        $pointScore = 0;
                    }
                }
                
                // Kontribusi point = (Score Point × Bobot Point) / 100
                $pointContribution = ($pointScore * $pointBobot) / 100;
                $kpiScore += $pointContribution;
            }
            
            $displayScore = $kpiScore;
            
            // ⚠️ PERBAIKAN: Kontribusi = (Nilai ÷ Bobot) × 100%
            $contribution = $kpiBobot > 0 ? ($displayScore / $kpiBobot) * 100 : 0;
            
            // ⚠️ PERBAIKAN: Status berdasarkan KONTRIBUSI
            $status = $this->getStatusByContribution($contribution);
            
            $kpiDetails[] = [
                'aspek_kpi' => $kpi['nama'],
                'bobot' => $kpiBobot,
                'score' => round($displayScore, 2), // Nilai (0-100)
                'contribution' => round($contribution, 2), // Kontribusi = (Nilai ÷ Bobot) × 100%
                'achievement_percentage' => round($contribution, 2), // Progress = Kontribusi (sama dengan kontribusi)
                'status' => $status
            ];
            
            $totalScore += $displayScore;
            $totalBobot += $kpiBobot;
        }

        // Average score = total nilai / jumlah aspek
        $averageScore = count($kpiDetails) > 0 ? ($totalScore / count($kpiDetails)) : 0;
        
        // Rata-rata kontribusi
        $averageContribution = count($kpiDetails) > 0 ? 
            (array_sum(array_column($kpiDetails, 'contribution')) / count($kpiDetails)) : 0;

        // Hitung ranking
        $ranking = 1;
        $totalEmployees = Employee::where('status', 'Aktif')->count();

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
                    'average_score' => round($averageScore, 2),
                    'average_contribution' => round($averageContribution, 2),
                    'performance_status' => $this->getStatusByContribution($averageContribution),
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

private function getStatusByContribution($contribution)
{
    $numericContribution = floatval($contribution);
    
    if ($numericContribution >= 90) return 'Sangat Baik';
    if ($numericContribution >= 80) return 'Baik';
    if ($numericContribution >= 70) return 'Cukup';
    if ($numericContribution >= 50) return 'Kurang';
    return 'Sangat Kurang';
}

// Helper function untuk menentukan status score
private function getScoreStatus($score)
{
    if ($score >= 90) return 'Excellent';
    if ($score >= 80) return 'Good';
    if ($score >= 70) return 'Average';
    return 'Poor';
}

}