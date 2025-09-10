<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kpi;
use App\Models\KpiPoint;
use App\Models\KpiQuestion;
use App\Models\KpiQuestionHasEmployee;
use App\Models\KpiHasEmployee;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiController extends Controller
{
    /**
     * Ambil semua KPI beserta sub-aspek dan pertanyaan
     */
    public function getAllKpis()
    {
        return response()->json(Kpi::with('points.questions')->get());
    }

    /**
     * Simpan KPI beserta sub-aspek dan pertanyaan
     */
    public function storeKpi(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'bobot' => 'required|numeric|min:0|max:100',
            'is_global' => 'required|boolean',
            'points' => 'required|array|min:1',
            'points.*.nama' => 'required|string|max:255',
            'points.*.bobot' => 'required|numeric|min:0|max:100',
            'points.*.questions' => 'required|array|min:1',
            'points.*.questions.*' => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($request) {
            // Buat KPI utama
            $kpi = Kpi::create([
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'bobot' => $request->bobot,
                'is_global' => $request->is_global,
            ]);

            // Buat sub-aspek
            foreach ($request->points as $pointData) {
                $point = KpiPoint::create([
                    'kpis_id_kpi' => $kpi->id_kpi,
                    'nama' => $pointData['nama'],
                    'bobot' => $pointData['bobot'],
                ]);

                // Buat pertanyaan untuk setiap sub-aspek
                foreach ($pointData['questions'] as $qText) {
                    KpiQuestion::create([
                        'kpi_point_id' => $point->id_point,
                        'pertanyaan' => $qText
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'KPI berhasil dibuat'
        ]);
    }

    /**
     * Update KPI beserta sub-aspek dan pertanyaan
     */
    public function updateKpi(Request $request, $kpiId)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'bobot' => 'required|numeric|min:0|max:100',
            'is_global' => 'required|boolean',
            'points' => 'required|array|min:1',
            'points.*.id_point' => 'nullable|exists:kpi_points,id_point',
            'points.*.nama' => 'required|string|max:255',
            'points.*.bobot' => 'required|numeric|min:0|max:100',
            'points.*.questions' => 'required|array|min:1',
            'points.*.questions.*.id_question' => 'nullable|exists:kpi_questions,id_question',
            'points.*.questions.*.pertanyaan' => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $kpiId) {
            $kpi = Kpi::findOrFail($kpiId);
            $kpi->update([
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'bobot' => $request->bobot,
                'is_global' => $request->is_global,
            ]);

            foreach ($request->points as $pointData) {
                $point = isset($pointData['id_point'])
                    ? KpiPoint::findOrFail($pointData['id_point'])
                    : KpiPoint::create([
                        'kpis_id_kpi' => $kpi->id_kpi,
                        'nama' => $pointData['nama'],
                        'bobot' => $pointData['bobot'],
                    ]);

                $point->update(['nama' => $pointData['nama'], 'bobot' => $pointData['bobot']]);

                foreach ($pointData['questions'] as $qData) {
                    $question = isset($qData['id_question'])
                        ? KpiQuestion::findOrFail($qData['id_question'])
                        : new KpiQuestion(['kpi_point_id' => $point->id_point]);

                    $question->pertanyaan = $qData['pertanyaan'];
                    $question->kpi_point_id = $point->id_point;
                    $question->save();
                }
            }
        });

        return response()->json([
            'message' => 'KPI berhasil diperbarui'
        ]);
    }

    /**
     * Hapus KPI beserta sub-aspek dan pertanyaan
     */
    public function deleteKpi($kpiId)
    {
        $kpi = Kpi::findOrFail($kpiId);
        $kpi->delete(); // Soft delete akan otomatis cascade ke sub-aspek & pertanyaan jika relasi sudah pakai softDeletes
        return response()->json(['message' => 'KPI berhasil dihapus']);
    }

    /**
     * Simpan/update nilai karyawan per pertanyaan
     */
    public function storeEmployeeScore(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id_karyawan',
            'kpi_question_id' => 'required|exists:kpi_questions,id_question',
            'nilai' => 'required|integer|min:1|max:4'
        ]);

        $record = KpiQuestionHasEmployee::updateOrCreate(
            [
                'employees_id_karyawan' => $request->employee_id,
                'kpi_question_id_question' => $request->kpi_question_id,
            ],
            ['nilai' => $request->nilai]
        );

        return response()->json([
            'message' => 'Nilai berhasil disimpan',
            'data' => $record
        ]);
    }

    /**
     * Hitung nilai akhir KPI per karyawan
     */
    public function calculateFinalScore($employeeId, $tahun, $bulan)
    {
        $employee = Employee::findOrFail($employeeId);
        $kpis = Kpi::with('points.questions')->get();

        foreach ($kpis as $kpi) {
            $nilaiAkhir = 0;

            foreach ($kpi->points as $point) {
                $total = $point->questions->sum(function ($q) use ($employeeId) {
                    return KpiQuestionHasEmployee::where('kpi_question_id_question', $q->id_question)
                        ->where('employees_id_karyawan', $employeeId)
                        ->value('nilai') ?? 0;
                });
                $count = $point->questions->count();
                $avg = $count > 0 ? $total / $count : 0;
                $skorSubAspek = ($avg * 2.5) * ($point->bobot / 100);
                $nilaiAkhir += $skorSubAspek;
            }

            KpiHasEmployee::updateOrCreate(
                [
                    'kpis_id_kpi' => $kpi->id_kpi,
                    'employees_id_karyawan' => $employeeId,
                    'tahun' => $tahun,
                    'bulan' => $bulan
                ],
                ['nilai_akhir' => $nilaiAkhir]
            );
        }

        return response()->json(['message' => 'Nilai akhir KPI berhasil dihitung']);
    }

    /**
     * Ambil skor per aspek utama
     */
    public function getScoreByAspekUtama($employeeId, $tahun, $bulan)
    {
        $employee = Employee::findOrFail($employeeId);
        $kpis = Kpi::with('points')->get();
        $result = [];

        foreach ($kpis as $kpi) {
            $nilaiAkhir = KpiHasEmployee::where('kpis_id_kpi', $kpi->id_kpi)
                ->where('employees_id_karyawan', $employeeId)
                ->where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->value('nilai_akhir') ?? 0;

            $result[] = [
                'aspek_utama' => $kpi->nama,
                'bobot' => $kpi->bobot,
                'nilai' => $nilaiAkhir
            ];
        }

        return response()->json([
            'employee' => $employee->nama_karyawan,
            'scores' => $result
        ]);
    }
}
