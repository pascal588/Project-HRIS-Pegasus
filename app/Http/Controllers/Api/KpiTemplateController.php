<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kpi;
use App\Models\KpiPoint;
use App\Models\KpiQuestion;
use App\Models\Division;
use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class KpiTemplateController extends Controller
{
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

        $errors = [];
        foreach ($request->kpis as $kpiIndex => $kpiData) {
            foreach ($kpiData['points'] as $pointIndex => $pointData) {
                $pointName = $pointData['nama'] ?? '';
                $isAbsensi = stripos($pointName, 'absensi') !== false;
                $hasQuestions = !empty($pointData['questions']) && count($pointData['questions']) > 0;
    
                if (!$isAbsensi && !$hasQuestions) {
                    $errors["kpis.{$kpiIndex}.points.{$pointIndex}.questions"] = [
                        "Sub-aspek '{$pointName}' harus memiliki minimal 1 pertanyaan"
                    ];
                }

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

    public function listGlobalKpi()
    {
        $kpis = Kpi::where('is_global', true)->whereNull('periode_id')->with('points.questions')->get();
        return response()->json(['success' => true, 'data' => $kpis]);
    }

private function saveSingleKpi(array $kpiData, bool $isGlobal, $divisionId = null, $periodeId = null)
{
    $kpi = isset($kpiData['id_kpi']) ? Kpi::findOrFail($kpiData['id_kpi']) : new Kpi();
    $kpi->nama = $kpiData['nama'];
    $kpi->bobot = $kpiData['bobot'];

    // ⚠️ PERBAIKAN: Jangan ubah is_global jika KPI sudah ada
    if (!$kpi->exists) {
        $kpi->is_global = $isGlobal;
    } else {
        // Untuk KPI existing, pertahankan nilai is_global asli
        Log::info("Preserving existing is_global value: " . $kpi->is_global);
    }

    $kpi->periode_id = $periodeId;
    $kpi->save();

    Log::info("Saved KPI:", [
        'id' => $kpi->id_kpi,
        'nama' => $kpi->nama,
        'is_global' => $kpi->is_global,
        'periode_id' => $kpi->periode_id,
        'division_id' => $divisionId
    ]);

    if (isset($kpiData['points'])) {
        $existingPointIds = [];
        foreach ($kpiData['points'] as $pointData) {
            $point = isset($pointData['id_point']) ? KpiPoint::findOrFail($pointData['id_point']) : new KpiPoint();
            $point->kpis_id_kpi = $kpi->id_kpi;
            $point->nama = $pointData['nama'];
            $point->bobot = $pointData['bobot'];
            $point->save();
            $existingPointIds[] = $point->id_point;

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
                
                // ⚠️ PERBAIKAN: Hanya hapus questions jika bukan absensi
                if (!$isAbsensi) {
                    KpiQuestion::where('kpi_point_id', $point->id_point)
                        ->whereNotIn('id_question', $existingQuestionIds)
                        ->delete();
                }
            } else {
                // ⚠️ PERBAIKAN: Untuk absensi, jangan hapus questions (tidak ada questions)
                if (!$isAbsensi) {
                    KpiQuestion::where('kpi_point_id', $point->id_point)->delete();
                }
            }
        }
        
        // ⚠️ PERBAIKAN: Hanya hapus points yang tidak ada dalam data baru
        KpiPoint::where('kpis_id_kpi', $kpi->id_kpi)
            ->whereNotIn('id_point', $existingPointIds)
            ->delete();
    }

    // ⚠️ PERBAIKAN: Handle divisions dengan lebih baik
    if ($isGlobal) {
        $allDivisions = Division::pluck('id_divisi');
        foreach ($allDivisions as $divId) {
            DB::table('division_has_kpis')->updateOrInsert(
                ['id_divisi' => $divId, 'kpis_id_kpi' => $kpi->id_kpi],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
        
        Log::info("Assigned KPI to ALL divisions (Global)");
    } elseif ($divisionId) {
        // ⚠️ PERBAIKAN PENTING: Untuk KPI divisi, HAPUS hubungan dengan divisi lain
        DB::table('division_has_kpis')->where('kpis_id_kpi', $kpi->id_kpi)->delete();
        
        // Tambahkan hanya ke divisi yang dipilih
        DB::table('division_has_kpis')->updateOrInsert(
            ['id_divisi' => $divisionId, 'kpis_id_kpi' => $kpi->id_kpi],
            ['created_at' => now(), 'updated_at' => now()]
        );
        
        Log::info("Assigned KPI to specific division:", ['division_id' => $divisionId]);
    }

    return $kpi->load('points.questions');
}
}
