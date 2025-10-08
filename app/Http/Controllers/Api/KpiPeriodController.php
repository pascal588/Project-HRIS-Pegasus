<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kpi;
use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class KpiPeriodController extends Controller
{
    public function copyTemplatesToPeriod($periodId)
    {
        $period = Period::findOrFail($periodId);
        
        // ✅ PERBAIKAN: Cek dulu apakah sudah ada KPI di periode ini
        $existingKpis = Kpi::where('periode_id', $periodId)->get();
        
        if ($existingKpis->count() > 0) {
            Log::info('KPI already exist for period, skipping copy', [
                'period_id' => $periodId,
                'existing_kpis_count' => $existingKpis->count(),
                'existing_kpi_names' => $existingKpis->pluck('nama')
            ]);
            
            return response()->json([
                'success' => true, 
                'message' => 'KPI already exist for this period', 
                'existing_count' => $existingKpis->count()
            ]);
        }

        DB::beginTransaction();
        try {
            $copiedCount = 0;
            $templates = Kpi::whereNull('periode_id')->with('points.questions', 'divisions')->get();
            
            foreach ($templates as $template) {
                $this->copyKpiToPeriod($template, $periodId);
                $copiedCount++;
            }
            
            DB::commit();
            
            Log::info('KPI templates copied to period', [
                'period_id' => $periodId,
                'copied_count' => $copiedCount
            ]);
            
            return response()->json([
                'success' => true, 
                'message' => 'Copied to period', 
                'copied_count' => $copiedCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to copy KPI: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Failed copy: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getKpisByPeriod($periodId)
    {
        $period = Period::findOrFail($periodId);
        $kpis = Kpi::where('periode_id', $periodId)->with(['points.questions', 'divisions'])->get();
        return response()->json(['success' => true, 'data' => ['period' => $period, 'kpis' => $kpis]]);
    }

public function listKpiByDivision($divisionId, Request $request)
{
    $periodeId = $request->get('periode_id');

    Log::info("listKpiByDivision Debug:", [
        'division_id' => $divisionId,
        'periode_id' => $periodeId,
        'request_all' => $request->all()
    ]);

    // ✅ PRIORITAS: Ambil KPI dari PERIODE (bukan template)
    if ($periodeId) {
        // Ambil SEMUA KPI di periode tersebut (tanpa syarat published)
        $globalKpis = Kpi::where('periode_id', $periodeId)
            ->where('is_global', true)
            ->with(['points.questions'])
            ->get();

        Log::info("Global KPIs from PERIOD:", [
            'count' => $globalKpis->count(),
            'kpi_names' => $globalKpis->pluck('nama'),
            'period_id' => $periodeId
        ]);

        $divisionKpis = Kpi::where('periode_id', $periodeId)
            ->whereHas('divisions', function ($q) use ($divisionId) {
                $q->where('divisions.id_divisi', $divisionId);
            })
            ->with(['points.questions'])
            ->get();

        Log::info("Division KPIs from PERIOD:", [
            'count' => $divisionKpis->count(),
            'kpi_names' => $divisionKpis->pluck('nama'),
            'period_id' => $periodeId
        ]);

        // Gabungkan dan hapus duplikasi
        $allKpis = $globalKpis->merge($divisionKpis);
        $uniqueKpis = collect();
        $seenKpiIds = [];

        foreach ($allKpis as $kpi) {
            $kpiId = $kpi->id_kpi;
            if (!in_array($kpiId, $seenKpiIds)) {
                $seenKpiIds[] = $kpiId;
                $uniqueKpis->push($kpi);
            }
        }

        $kpis = $uniqueKpis;

        Log::info("Final KPI from PERIOD:", [
            'total_kpis' => $kpis->count(),
            'source' => 'PERIOD_DATA'
        ]);

    } else {
        // ❌ FALLBACK: Template KPI (HANYA jika tidak ada periode)
        Log::warning("Using TEMPLATE KPIs as fallback - No period ID provided");

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
        $allKpis = $globalKpis->merge($divisionKpis);
        $uniqueKpis = collect();
        $seenKpiIds = [];

        foreach ($allKpis as $kpi) {
            $kpiId = $kpi->id_kpi;
            if (!in_array($kpiId, $seenKpiIds)) {
                $seenKpiIds[] = $kpiId;
                $uniqueKpis->push($kpi);
            }
        }

        $kpis = $uniqueKpis;

        Log::info("Using TEMPLATE KPIs (fallback):", [
            'total_kpis' => $kpis->count(),
            'source' => 'TEMPLATE_FALLBACK'
        ]);
    }

    // Format response data
    $formattedKpis = [];
    foreach ($kpis as $kpi) {
        $pointsData = [];
        
        foreach ($kpi->points as $point) {
            $questionsData = [];
            
            foreach ($point->questions as $question) {
                $questionsData[] = [
                    'id_question' => $question->id_question,
                    'pertanyaan' => $question->pertanyaan,
                    'kpi_point_id' => $question->kpi_point_id
                ];
            }
            
            $pointsData[] = [
                'id_point' => $point->id_point,
                'nama' => $point->nama,
                'bobot' => (float)$point->bobot,
                'kpis_id_kpi' => $point->kpis_id_kpi,
                'questions' => $questionsData
            ];
        }
        
        $formattedKpis[] = [
            'id_kpi' => $kpi->id_kpi,
            'nama' => $kpi->nama,
            'bobot' => (float)$kpi->bobot,
            'is_global' => $kpi->is_global,
            'periode_id' => $kpi->periode_id,
            'points' => $pointsData
        ];
    }

    return response()->json([
        'success' => true,
        'data' => $formattedKpis,
        'debug' => [
            'division_id' => $divisionId,
            'periode_id' => $periodeId,
            'total_kpis' => count($formattedKpis),
            'kpi_names' => collect($formattedKpis)->pluck('nama'),
            'kpi_ids' => collect($formattedKpis)->pluck('id_kpi'),
            'data_source' => $periodeId ? 'PERIOD_DATA' : 'TEMPLATE_FALLBACK'
        ]
    ]);
}

    // HAPUS method publishToPeriod() atau simplify:
    public function publishToPeriod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period_id' => 'required|exists:periods,id_periode'
            // ❌ HAPUS deadline_days validation
        ]);

        DB::beginTransaction();
        try {
            $period = Period::findOrFail($request->period_id);

            // ✅ SIMPLE UPDATE - tanpa deadline
            $period->update([
                'kpi_published' => true,
                'kpi_published_at' => now(),
                'status' => 'active'
            ]);

            // Copy templates jika belum ada
            $existingKpisCount = Kpi::where('periode_id', $period->id_periode)->count();
            if ($existingKpisCount === 0) {
                $this->copyTemplatesToPeriod($period->id_periode);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'KPI berhasil dipublish untuk periode ' . $period->nama
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
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

    public function getPublishedPeriods()
    {
        try {
            $periods = Period::where('kpi_published', true)
                ->orderBy('tanggal_mulai', 'desc')
                ->get(['id_periode', 'nama', 'tanggal_mulai', 'tanggal_selesai', 'status']);

            return response()->json([
                'success' => true,
                'data' => $periods
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching periods: ' . $e->getMessage()
            ], 500);
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
}
