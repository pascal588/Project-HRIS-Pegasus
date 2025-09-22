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
            'kpis.*.points' => 'required|array|min:1',
            'kpis.*.points.*.nama' => 'required|string|max:255',
            'kpis.*.points.*.bobot' => 'required|numeric|min:0|max:100',
            'kpis.*.points.*.questions' => 'required|array|min:1',
            'kpis.*.points.*.questions.*.pertanyaan' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $savedKpis = [];
            foreach ($request->kpis as $kpiData) {
                $kpi = $this->saveSingleKpi($kpiData, $request->is_global, $request->division_id, null);
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
        $kpi->is_global = $isGlobal;
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

    // ================== KPI BY DIVISION / GLOBAL ==================
    public function listKpiByDivision($divisionId)
    {
        $global = Kpi::where('is_global', true)->whereNull('periode_id')->with('points.questions')->get();
        $div = Kpi::whereHas('divisions', function ($q) use ($divisionId) {
            $q->where('divisions.id_divisi', $divisionId);
        })
            ->whereNull('periode_id')->with('points.questions')->get();
        return response()->json(['success' => true, 'data' => $global->concat($div)->unique('id_kpi')->values()]);
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

    // ================== EMPLOYEE SCORE ==================
    public function storeEmployeeScore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_karyawan' => 'required|exists:employees,id_karyawan',
            'periode_id' => 'required|exists:periods,id_periode',
            'hasil' => 'required|array|min:1',
            'hasil.*.jawaban' => 'required|array|min:1',
            'hasil.*.jawaban.*.id' => 'required|exists:kpi_questions,id_question',
            'hasil.*.jawaban.*.jawaban' => 'required|integer|min:1|max:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $period = Period::findOrFail($request->periode_id);
        if (!$period->canBeEvaluated()) {
            return response()->json(['success' => false, 'message' => 'Cannot input score, period status: ' . $period->status], 400);
        }

        DB::beginTransaction();
        try {
            foreach ($request->hasil as $aspek) {
                foreach ($aspek['jawaban'] as $jawaban) {
                    KpiQuestionHasEmployee::updateOrCreate(
                        ['employees_id_karyawan' => $request->id_karyawan, 'kpi_question_id_question' => $jawaban['id'], 'periode_id' => $request->periode_id],
                        ['nilai' => $jawaban['jawaban']]
                    );
                }
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Scores saved']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed save score: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed save score'], 500);
        }
    }

    // ================== CALCULATE FINAL SCORE ==================
    public function calculateFinalScore($employeeId, $periodeId)
    {
        $period = Period::findOrFail($periodeId);
        $employee = Employee::findOrFail($employeeId);

        $kpis = Kpi::where('periode_id', $periodeId)->with(['points.questions'])->get();
        $result = [];

        foreach ($kpis as $kpi) {
            $aspekTotal = 0;
            foreach ($kpi->points as $point) {
                $pointTotal = 0;
                foreach ($point->questions as $q) {
                    $score = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                        ->where('kpi_question_id_question', $q->id_question)
                        ->where('periode_id', $periodeId)
                        ->first()?->nilai ?? 0;
                    $pointTotal += $score * ($point->bobot / 100);
                }
                $aspekTotal += $pointTotal * ($kpi->bobot / 100);
            }
            $result[] = ['aspek' => $kpi->nama, 'score' => $aspekTotal];
        }

        $totalScore = array_sum(array_column($result, 'score'));

        return response()->json(['success' => true, 'data' => ['scores' => $result, 'total' => $totalScore]]);
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

    // ================== GET EMPLOYEE KPI FOR PERIOD ==================
    public function getEmployeeKpiForPeriod($employeeId, $periodId)
    {
        $kpis = Kpi::where('periode_id', $periodId)->with(['points.questions'])->get();
        $data = [];
        foreach ($kpis as $kpi) {
            $pointsData = [];
            foreach ($kpi->points as $point) {
                $questionsData = [];
                foreach ($point->questions as $q) {
                    $answer = KpiQuestionHasEmployee::where('employees_id_karyawan', $employeeId)
                        ->where('kpi_question_id_question', $q->id_question)
                        ->where('periode_id', $periodId)
                        ->first()?->nilai ?? null;
                    $questionsData[] = ['question' => $q->pertanyaan, 'answer' => $answer];
                }
                $pointsData[] = ['point' => $point->nama, 'bobot' => $point->bobot, 'questions' => $questionsData];
            }
            $data[] = ['aspek' => $kpi->nama, 'bobot' => $kpi->bobot, 'points' => $pointsData];
        }
        return response()->json(['success' => true, 'data' => $data]);
    }

    // ================== ATTENDANCE SUMMARY ==================
    public function getAttendanceSummary($employeeId, $periodeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $period = Period::findOrFail($periodeId);

        $attendances = Attendance::where('employee_id', $employeeId)->where('periode_id', $periodeId)->get();

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

        return response()->json(['success' => true, 'data' => ['employee' => $employee->nama, 'period' => $period->nama, 'attendance_summary' => $summary]]);
    }
}
