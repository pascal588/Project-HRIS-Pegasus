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
            // 'kpis.*.points.*.attendance_multipliers' => 'sometimes|array',
            // 'kpis.*.points.*.attendance_multipliers.hadir_multiplier' => 'sometimes|numeric',
            // 'kpis.*.points.*.attendance_multipliers.sakit_multiplier' => 'sometimes|numeric',
            // 'kpis.*.points.*.attendance_multipliers.izin_multiplier' => 'sometimes|numeric',
            // 'kpis.*.points.*.attendance_multipliers.mangkir_multiplier' => 'sometimes|numeric',
            // 'kpis.*.points.*.attendance_multipliers.terlambat_multiplier' => 'sometimes|numeric',
            // 'kpis.*.points.*.attendance_multipliers.workday_multiplier' => 'sometimes|numeric|min:1',
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
                $isAbsensi = stripos($pointName, 'absensi') !== false || stripos($pointName, 'kehadiran') !== false;
                $hasQuestions = !empty($pointData['questions']) && count($pointData['questions']) > 0;
    
                // Validasi: Untuk sub-aspek absensi, harus ada konfigurasi multiplier
                if ($isAbsensi && empty($pointData['attendance_multipliers'])) {
                    $errors["kpis.{$kpiIndex}.points.{$pointIndex}.attendance_multipliers"] = [
                        "Sub-aspek absensi '{$pointName}' harus memiliki konfigurasi multiplier"
                    ];
                }

                // Validasi: Untuk non-absensi, harus ada pertanyaan
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

    public function getKpiByDivision($divisionId)
    {
        try {
            Log::info("Getting KPI for division: {$divisionId}");

            // Ambil KPI global
            $globalKpis = Kpi::where('is_global', true)
                ->whereNull('periode_id')
                ->with(['points.questions'])
                ->get();

            // Ambil KPI spesifik divisi
            $divisionKpis = Kpi::whereHas('divisions', function ($q) use ($divisionId) {
                $q->where('divisions.id_divisi', $divisionId);
            })
                ->whereNull('periode_id')
                ->with(['points.questions'])
                ->get();

            // Gabungkan hasil
            $allKpis = $globalKpis->merge($divisionKpis);

            Log::info("KPI results:", [
                'division_id' => $divisionId,
                'global_count' => $globalKpis->count(),
                'division_count' => $divisionKpis->count(),
                'total_count' => $allKpis->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => $allKpis,
                'debug_info' => [
                    'division_id' => $divisionId,
                    'global_kpis' => $globalKpis->count(),
                    'division_kpis' => $divisionKpis->count(),
                    'total_kpis' => $allKpis->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting KPI by division: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting KPI: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAttendanceConfig($pointId)
    {
        try {
            $point = KpiPoint::findOrFail($pointId);
            
            $isAbsensi = stripos($point->nama, 'absensi') !== false || 
                        stripos($point->nama, 'kehadiran') !== false;

            if (!$isAbsensi) {
                return response()->json([
                    'success' => false,
                    'message' => 'This point is not an attendance point'
                ], 400);
            }

            // Ambil konfigurasi dari database
            $config = $this->extractAttendanceConfig($point);

            return response()->json([
                'success' => true,
                'data' => $config
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting attendance config: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading attendance config: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract attendance configuration from KPI point
     */
    private function extractAttendanceConfig($point)
    {
        // Default configuration
        $defaultConfig = [
            'hadir_multiplier' => 3,
            'sakit_multiplier' => 0,
            'izin_multiplier' => 0,
            'mangkir_multiplier' => -3,
            'terlambat_multiplier' => -2,
            'workday_multiplier' => 2
        ];

        try {
            // Coba ambil dari kolom attendance_config (JSON)
            if (!empty($point->attendance_config)) {
                $config = json_decode($point->attendance_config, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($config)) {
                    return array_merge($defaultConfig, $config);
                }
            }

            // Coba parse dari kolom description atau nama
            $pointName = strtolower($point->nama);
            
            // Custom logic berdasarkan nama point
            if (strpos($pointName, 'ketat') !== false) {
                return [
                    'hadir_multiplier' => 4,
                    'sakit_multiplier' => -1,
                    'izin_multiplier' => -1,
                    'mangkir_multiplier' => -5,
                    'terlambat_multiplier' => -3,
                    'workday_multiplier' => 2
                ];
            } elseif (strpos($pointName, 'longgar') !== false) {
                return [
                    'hadir_multiplier' => 2,
                    'sakit_multiplier' => 1,
                    'izin_multiplier' => 1,
                    'mangkir_multiplier' => -2,
                    'terlambat_multiplier' => -1,
                    'workday_multiplier' => 2
                ];
            }

            return $defaultConfig;

        } catch (\Exception $e) {
            Log::error('Error extracting attendance config: ' . $e->getMessage());
            return $defaultConfig;
        }
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
            
            // ⚠️ PERBAIKAN PENTING: Simpan konfigurasi absensi dengan handle nilai 0
            $isAbsensi = stripos($pointData['nama'], 'absensi') !== false ||
                        stripos($pointData['nama'], 'kehadiran') !== false;

            if ($isAbsensi && isset($pointData['attendance_multipliers'])) {
                $multipliers = $pointData['attendance_multipliers'];
                
                // ⚠️ FIX: Handle nilai 0 dengan benar - jangan skip!
                $validMultipliers = [
                    'hadir_multiplier' => isset($multipliers['hadir_multiplier']) ? (int)$multipliers['hadir_multiplier'] : 3,
                    'sakit_multiplier' => isset($multipliers['sakit_multiplier']) ? (int)$multipliers['sakit_multiplier'] : 0,
                    'izin_multiplier' => isset($multipliers['izin_multiplier']) ? (int)$multipliers['izin_multiplier'] : 0,
                    'mangkir_multiplier' => isset($multipliers['mangkir_multiplier']) ? (int)$multipliers['mangkir_multiplier'] : -3,
                    'terlambat_multiplier' => isset($multipliers['terlambat_multiplier']) ? (int)$multipliers['terlambat_multiplier'] : -2,
                    'workday_multiplier' => isset($multipliers['workday_multiplier']) && $multipliers['workday_multiplier'] >= 1 
                        ? (int)$multipliers['workday_multiplier'] 
                        : 2
                ];
                
                $point->attendance_config = json_encode($validMultipliers);
                
                Log::info("💾 SAVING ATTENDANCE CONFIG:", [
                    'point_id' => $point->id_point,
                    'point_name' => $point->nama,
                    'multipliers_received' => $multipliers,
                    'multipliers_saved' => $validMultipliers,
                    'has_zero_values' => in_array(0, $validMultipliers) || in_array(-0, $validMultipliers)
                ]);
            } else {
                // Reset jika bukan absensi
                $point->attendance_config = null;
                
                Log::info("Not saving attendance config - not absensi point:", [
                    'point_name' => $point->nama,
                    'is_absensi' => $isAbsensi,
                    'has_multipliers' => isset($pointData['attendance_multipliers'])
                ]);
            }
            
            $point->save();
            $existingPointIds[] = $point->id_point;

            // Log detail point
            Log::info("Saved KPI Point:", [
                'point_id' => $point->id_point,
                'point_name' => $point->nama,
                'is_absensi' => $isAbsensi,
                'has_attendance_config' => !empty($point->attendance_config),
                'attendance_config' => $point->attendance_config
            ]);

            // Handle questions hanya untuk non-absensi
            if (!$isAbsensi && isset($pointData['questions']) && count($pointData['questions']) > 0) {
                $existingQuestionIds = [];
                foreach ($pointData['questions'] as $qData) {
                    $question = isset($qData['id_question']) ? KpiQuestion::findOrFail($qData['id_question']) : new KpiQuestion();
                    $question->kpi_point_id = $point->id_point;
                    $question->pertanyaan = $qData['pertanyaan'];
                    $question->save();
                    $existingQuestionIds[] = $question->id_question;
                }
                
                // Hapus questions yang tidak ada dalam data baru
                KpiQuestion::where('kpi_point_id', $point->id_point)
                    ->whereNotIn('id_question', $existingQuestionIds)
                    ->delete();
                    
                Log::info("Saved questions for point:", [
                    'point_id' => $point->id_point,
                    'questions_count' => count($existingQuestionIds)
                ]);
            } elseif ($isAbsensi) {
                // Untuk absensi, hapus semua questions (absensi tidak butuh questions)
                $deletedCount = KpiQuestion::where('kpi_point_id', $point->id_point)->delete();
                Log::info("Cleared questions for absensi point:", [
                    'point_id' => $point->id_point,
                    'questions_deleted' => $deletedCount
                ]);
            } else {
                // Untuk non-absensi tanpa questions, hapus semua questions
                $deletedCount = KpiQuestion::where('kpi_point_id', $point->id_point)->delete();
                Log::info("Cleared questions for non-absensi point:", [
                    'point_id' => $point->id_point,
                    'questions_deleted' => $deletedCount
                ]);
            }
        }
        
        // ⚠️ PERBAIKAN: Hanya hapus points yang tidak ada dalam data baru
        $deletedPointsCount = KpiPoint::where('kpis_id_kpi', $kpi->id_kpi)
            ->whereNotIn('id_point', $existingPointIds)
            ->delete();
            
        Log::info("Cleaned up old points:", [
            'kpi_id' => $kpi->id_kpi,
            'points_deleted' => $deletedPointsCount
        ]);
    }

    // ⚠️ PERBAIKAN: Handle divisions dengan lebih baik
    if ($kpi->is_global) {
        // Untuk KPI global, assign ke semua divisi
        $allDivisions = Division::pluck('id_divisi');
        foreach ($allDivisions as $divId) {
            DB::table('division_has_kpis')->updateOrInsert(
                ['id_divisi' => $divId, 'kpis_id_kpi' => $kpi->id_kpi],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
        
        Log::info("Assigned KPI to ALL divisions (Global)", [
            'kpi_id' => $kpi->id_kpi,
            'divisions_count' => $allDivisions->count()
        ]);
    } elseif ($divisionId) {
        // ⚠️ PERBAIKAN PENTING: Untuk KPI divisi, HAPUS hubungan dengan divisi lain
        $deletedRelations = DB::table('division_has_kpis')->where('kpis_id_kpi', $kpi->id_kpi)->delete();
        
        // Tambahkan hanya ke divisi yang dipilih
        DB::table('division_has_kpis')->updateOrInsert(
            ['id_divisi' => $divisionId, 'kpis_id_kpi' => $kpi->id_kpi],
            ['created_at' => now(), 'updated_at' => now()]
        );
        
        Log::info("Assigned KPI to specific division:", [
            'kpi_id' => $kpi->id_kpi,
            'division_id' => $divisionId,
            'old_relations_deleted' => $deletedRelations
        ]);
    } else {
        Log::warning("KPI has no division assignment:", [
            'kpi_id' => $kpi->id_kpi,
            'is_global' => $kpi->is_global,
            'division_id_param' => $divisionId
        ]);
    }

    // Load relasi untuk return
    $kpi->load(['points.questions', 'divisions']);

    Log::info("✅ Successfully saved KPI with all relations", [
        'kpi_id' => $kpi->id_kpi,
        'points_count' => $kpi->points->count(),
        'divisions_count' => $kpi->divisions->count()
    ]);

    return $kpi;
}

    /**
     * Helper method untuk mendapatkan konfigurasi absensi dari point ID
     */
    public static function getAttendanceConfigByPointId($pointId)
    {
        try {
            $point = KpiPoint::find($pointId);
            if (!$point) {
                return null;
            }

            $controller = new self();
            return $controller->extractAttendanceConfig($point);

        } catch (\Exception $e) {
            Log::error('Error getting attendance config by point ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update konfigurasi absensi untuk point tertentu
     */
    public function updateAttendanceConfig(Request $request, $pointId)
    {
        $validator = Validator::make($request->all(), [
            'hadir_multiplier' => 'required|numeric',
            'sakit_multiplier' => 'required|numeric',
            'izin_multiplier' => 'required|numeric',
            'mangkir_multiplier' => 'required|numeric',
            'terlambat_multiplier' => 'required|numeric',
            'workday_multiplier' => 'required|numeric|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $point = KpiPoint::findOrFail($pointId);
            
            $isAbsensi = stripos($point->nama, 'absensi') !== false || 
                        stripos($point->nama, 'kehadiran') !== false;

            if (!$isAbsensi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya point absensi yang dapat diupdate konfigurasinya'
                ], 400);
            }

            // Simpan konfigurasi
            $point->attendance_config = json_encode($request->all());
            $point->save();

            Log::info("Updated attendance config for point:", [
                'point_id' => $pointId,
                'config' => $request->all()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi absensi berhasil diupdate',
                'data' => $request->all()
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating attendance config: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate konfigurasi: ' . $e->getMessage()
            ], 500);
        }
    }
}