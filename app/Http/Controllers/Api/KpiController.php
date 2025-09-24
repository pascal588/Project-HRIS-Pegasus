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
            'kpis.*.is_global' => 'required|boolean', // Tambahkan validasi ini
            'kpis.*.points' => 'required|array|min:1',
            'kpis.*.points.*.nama' => 'required|string|max:255',
            'kpis.*.points.*.bobot' => 'required|numeric|min:0|max:100',
            'kpis.*.points.*.questions' => 'required|array|min:1',
            'kpis.*.points.*.questions.*.pertanyaan' => 'required|string|max:1000',
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

        DB::beginTransaction();
        try {
            $savedKpis = [];
            foreach ($request->kpis as $kpiData) {
                // ⚠️ FIX: Gunakan is_global dari masing-masing KPI, bukan dari request utama
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

        // ⚠️ FIX: Hanya update is_global jika KPI baru
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

                if (isset($pointData['questions'])) {
                    $existingQuestionIds = [];
                    foreach ($pointData['questions'] as $qData) {
                        $question = isset($qData['id_question']) ? KpiQuestion::findOrFail($qData['id_question']) : new KpiQuestion();
                        $question->kpi_point_id = $point->id_point;
                        $question->pertanyaan = $qData['pertanyaan'];
                        $question->save();
                        $existingQuestionIds[] = $question->id_question;
                    }
                    KpiQuestion::where('kpi_point_id', $point->id_point)->whereNotIn('id_question', $existingQuestionIds)->delete();
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
    // ================== KPI BY DIVISION / GLOBAL ==================
    public function listKpiByDivision($divisionId, Request $request)
    {
        $periodeId = $request->get('periode_id');

        // Jika ada parameter periode_id, filter berdasarkan periode
        if ($periodeId) {
            $kpis = Kpi::where('periode_id', $periodeId)
                ->where(function ($query) use ($divisionId) {
                    $query->where('is_global', true)
                        ->orWhereHas('divisions', function ($q) use ($divisionId) {
                            $q->where('divisions.id_divisi', $divisionId);
                        });
                })
                ->with('points.questions')
                ->get();

            // Debug log
            log::info("KPI Query for Division {$divisionId}, Period {$periodeId}: Found " . $kpis->count() . " KPIs");
        } else {
            // Default: template KPI (null periode_id)
            $global = Kpi::where('is_global', true)
                ->whereNull('periode_id')
                ->with('points.questions')
                ->get();

            $div = Kpi::whereHas('divisions', function ($q) use ($divisionId) {
                $q->where('divisions.id_divisi', $divisionId);
            })
                ->whereNull('periode_id')
                ->with('points.questions')
                ->get();

            $kpis = $global->concat($div)->unique('id_kpi')->values();
        }

        return response()->json(['success' => true, 'data' => $kpis]);
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

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

            // 1️⃣ Simpan jawaban pertanyaan
            foreach ($request->hasil as $aspekIndex => $aspek) {
                Log::info("Processing Aspek {$aspekIndex}: {$aspek['id_aspek']}");
                foreach ($aspek['jawaban'] as $jawabanIndex => $jawaban) {
                    $saved = KpiQuestionHasEmployee::updateOrCreate(
                        [
                            'employees_id_karyawan' => $employeeId,
                            'kpi_question_id_question' => $jawaban['id'],
                            'periode_id' => $periodeId
                        ],
                        ['nilai' => $jawaban['jawaban'], 'updated_at' => now()]
                    );

                    if ($saved) {
                        $savedCount++;
                        Log::info("✅ Jawaban disimpan: Question ID {$jawaban['id']}, Nilai {$jawaban['jawaban']}");
                    } else {
                        Log::error("❌ Gagal simpan jawaban: Question ID {$jawaban['id']}");
                    }
                }
            }

            // 2️⃣ Ambil absensi
            $attendances = Attendance::where('employee_id', $employeeId)
                ->where('periode_id', $periodeId)
                ->get();

            $totalDays = $attendances->count();
            $hadir = $attendances->where('status', 'Present at workday (PW)')->count();
            $mangkir = $attendances->where('status', 'Absent (A)')->count();
            Log::info("Absensi -> Total: $totalDays, Hadir: $hadir, Mangkir: $mangkir");

            // 3️⃣ Hitung nilai akhir per KPI/aspek
            $kpis = Kpi::where('periode_id', $periodeId)->with(['points.questions'])->get();
            $aspectScores = [];

            foreach ($kpis as $kpiIndex => $kpi) {
                $aspekTotal = 0;
                Log::info("Processing KPI {$kpiIndex}: {$kpi->nama} (ID {$kpi->id})");

                foreach ($kpi->points as $pointIndex => $point) {
                    $pointTotal = 0;
                    Log::info("  Point {$pointIndex}: {$point->nama} (Bobot {$point->bobot})");

                    foreach ($point->questions as $qIndex => $q) {
                        $score = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                            ->where('kpi_question_id_question', $q->id_question)
                            ->where('periode_id', $periodeId)
                            ->first()?->nilai ?? 0;

                        $pointTotal += $score;
                        Log::info("    Question {$qIndex} (ID {$q->id_question}) -> Nilai: $score");
                    }

                    $avgQuestionScore = $point->questions->count() > 0 ? $pointTotal / $point->questions->count() : 0;

                    if ($point->is_absensi) {
                        $x = ($hadir * 3) + ($mangkir * -2);
                        $y = $totalDays * 2;
                        $attendancePercent = $y > 0 ? ($x / $y) * 100 : 0;

                        if ($attendancePercent >= 100) $scale = 10;
                        elseif ($attendancePercent >= 90) $scale = 8;
                        elseif ($attendancePercent >= 80) $scale = 6;
                        elseif ($attendancePercent >= 65) $scale = 4;
                        elseif ($attendancePercent >= 50) $scale = 2;
                        else $scale = 0;

                        $subFinal = $scale * ($point->bobot / 100);
                        Log::info("    Absensi -> Percent: $attendancePercent, Scale: $scale, SubFinal: $subFinal");
                    } else {
                        $subFinal = $avgQuestionScore * ($point->bobot / 100);
                        Log::info("    Non-Absensi -> AvgScore: $avgQuestionScore, SubFinal: $subFinal");
                    }

                    $aspekTotal += $subFinal;
                }

                // 4️⃣ Simpan ke kpis_has_employees (per KPI/aspek)
                DB::table('kpis_has_employees')->updateOrInsert(
                    [
                        'kpis_id_kpi' => $kpi->id_kpi,
                        'employees_id_karyawan' => $employeeId,
                        'periode_id' => $periodeId,
                    ],
                    [
                        'nilai_akhir' => $aspekTotal,
                        'tahun' => $tahun,
                        'bulan' => $bulan,
                        'updated_at' => now(),
                    ]
                );

                Log::info("✅ KPI '{$kpi->nama}' disimpan -> Nilai Akhir: $aspekTotal");
                $aspectScores[] = ['aspek' => $kpi->nama, 'score' => $aspekTotal];
            }

            DB::commit();
            Log::info("=== END KPI SCORE SAVE === Total jawaban disimpan: $savedCount");

            return response()->json([
                'success' => true,
                'message' => 'Jawaban dan nilai akhir KPI berhasil disimpan!',
                'saved_count' => $savedCount,
                'scores' => $aspectScores
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Gagal menyimpan nilai: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan nilai: ' . $e->getMessage()
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

    // ================== CALCULATE FINAL KPI =================


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
        $kpis = Kpi::where('periode_id', $periodId)
            ->with(['points.questions'])
            ->get();

        $data = [];

        foreach ($kpis as $kpi) {
            $pointsData = [];

            foreach ($kpi->points as $point) {
                $questionsData = [];

                foreach ($point->questions as $q) {
                    // ✅ PERBAIKAN: Tambahkan filter periode_id
                    $answer = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                        ->where('kpi_question_id_question', $q->id_question)
                        ->where('periode_id', $periodId) // ✅ FILTER PERIODE
                        ->first();

                    $questionsData[] = [
                        'id' => $q->id_question,
                        'pertanyaan' => $q->pertanyaan,
                        'answer' => $answer ? $answer->nilai : null
                    ];
                }

                $pointsData[] = [
                    'id' => $point->id_point,
                    'nama' => $point->nama,
                    'bobot' => $point->bobot,
                    'questions' => $questionsData
                ];
            }

            $data[] = [
                'id' => $kpi->id_kpi,
                'aspek' => $kpi->nama,
                'nama' => $kpi->nama,
                'bobot' => $kpi->bobot,
                'points' => $pointsData
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data
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
}
