<?php

// app/Http/Controllers/Api/KpiSystemApiController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Role;
use App\Models\Kpi;
use App\Models\KpiQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiController extends Controller
{
    // 🔹 List role berdasarkan divisi
    public function getRolesByDivision($divisionId)
    {
        $roles = Role::where('division_id', $divisionId)->get();

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    // 🔹 Tambah KPI (aspek) untuk divisi
    public function storeKpi(Request $request, $divisionId)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'bobot' => 'required|numeric|min:0|max:100',
        ]);

        $division = Division::findOrFail($divisionId);
        $kpi = Kpi::create($validated);

        // Hubungkan KPI dengan divisi
        $division->kpis()->attach($kpi->id_kpi);

        return response()->json([
            'success' => true,
            'message' => 'KPI berhasil ditambahkan ke divisi',
            'data' => $kpi
        ], 201);
    }

    // 🔹 Tambah pertanyaan ke KPI (aspek)
    public function storeQuestion(Request $request, $kpiId)
    {
        $validated = $request->validate([
            'pertanyaan' => 'required|string|max:255',
            'poin' => 'required|in:1,2,3,4',
        ]);

        $kpi = Kpi::findOrFail($kpiId);
        $question = $kpi->questions()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pertanyaan berhasil ditambahkan',
            'data' => $question
        ], 201);
    }

    // 🔹 List KPI + pertanyaan per divisi
    public function getKpisByDivision($divisionId)
    {
        $division = Division::with(['kpis.questions'])->findOrFail($divisionId);

        return response()->json([
            'success' => true,
            'data' => $division->kpis
        ]);
    }

    // 🔹 List KPI + pertanyaan per role
    public function getKpisByRole($roleId)
    {
        $role = Role::with(['kpis.questions'])->findOrFail($roleId);

        return response()->json([
            'success' => true,
            'role' => $role->nama_jabatan,
            'division' => $role->division->nama_divisi,
            'kpis' => $role->kpis
        ]);
    }

public function saveKpi(Request $request)
    {
        $validated = $request->validate([
            'divisionId' => 'required|exists:divisions,id_divisi',
            'roleId' => 'required|exists:roles,id_jabatan',
            'topics' => 'required|array',
            'topics.*.topicName' => 'required|string|max:100',
            'topics.*.topicWeight' => 'required|numeric|min:0|max:100',
            'topics.*.questions' => 'array',
        ]);
        
        DB::beginTransaction();
        
        try {
            $divisionId = $request->divisionId;
            $roleId = $request->roleId;
            $topics = $request->topics;
            
            // First, remove any existing KPIs for this role
            $role = Role::find($roleId);
            $role->kpis()->delete();
            
            // Create or update KPIs
            foreach ($topics as $topic) {
                // Create new KPI
                $kpi = Kpi::create([
                    'nama' => $topic['topicName'],
                    'deskripsi' => $topic['topicName'],
                    'bobot' => $topic['topicWeight'],
                    'role_id' => $roleId
                ]);
                
                // Attach KPI to division
                $kpi->divisions()->attach($divisionId);
                
                // Add questions
                if (!empty($topic['questions'])) {
                    foreach ($topic['questions'] as $questionText) {
                        $kpi->questions()->create([
                            'pertanyaan' => $questionText,
                            'poin' => 1, // Default value
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'KPI berhasil disimpan'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan KPI: ' . $e->getMessage()
            ], 500);
        }
    }
    
// 🔹 Delete KPI (aspek) from role
    public function deleteKpi($divisionId, $kpiId)
    {
        $kpi = Kpi::findOrFail($kpiId);
        
        // Detach from division
        $kpi->divisions()->detach($divisionId);
        
        // Delete the KPI if it's not used by any other division
        if ($kpi->divisions()->count() === 0) {
            $kpi->questions()->delete();
            $kpi->delete();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'KPI berhasil dihapus'
        ]);
    }

    // 🔹 Delete KPI question
    public function deleteQuestion($questionId)
    {
        $question = KpiQuestion::findOrFail($questionId);
        $question->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Pertanyaan berhasil dihapus'
        ]);
    }
};

