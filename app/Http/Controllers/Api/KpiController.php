<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kpi;
use App\Models\KpiPoint;
use App\Models\KpiQuestion;
use App\Models\KpiQuestionHasEmployee;
use App\Models\KpiHasEmployee;
use App\Models\Employee;
use App\Models\Attendance;
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
        $isBulk = $request->has('kpis');

        if ($isBulk) {
            $request->validate([
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

            DB::transaction(function () use ($request) {
                foreach ($request->kpis as $kpiData) {
                    $this->saveSingleKpi($kpiData, $request->is_global, $request->division_id);
                }
            });

            return response()->json(['message' => 'Semua KPI berhasil disimpan!']);
        } else {
            $request->validate([
                'nama' => 'required|string|max:100',
                'deskripsi' => 'nullable|string',
                'bobot' => 'required|numeric|min:0|max:100',
                'is_global' => 'required|boolean',
                'division_id' => 'nullable|integer|exists:divisions,id_divisi',
                'points' => 'required|array|min:1',
                'points.*.nama' => 'required|string|max:255',
                'points.*.bobot' => 'required|numeric|min:0|max:100',
                'points.*.questions' => 'required|array|min:1',
                'points.*.questions.*' => 'required|string|max:1000',
            ]);

            DB::transaction(function () use ($request) {
                $this->saveSingleKpi($request->all(), $request->is_global, $request->division_id);
            });

            return response()->json(['message' => 'KPI berhasil disimpan!']);
        }
    }

    /**
     * Helper: simpan 1 KPI (bisa dipakai single/bulk)
     */
    private function saveSingleKpi(array $kpiData, bool $isGlobal, $divisionId = null)
    {
        $kpi = isset($kpiData['id_kpi'])
            ? Kpi::findOrFail($kpiData['id_kpi'])
            : new Kpi();

        $kpi->nama = $kpiData['nama'];
        $kpi->deskripsi = $kpiData['deskripsi'] ?? null;
        $kpi->bobot = $kpiData['bobot'];
        if (!$kpi->exists) {
            $kpi->is_global = $isGlobal;
        } else {
        }
        $kpi->save();

        foreach ($kpiData['points'] as $pointData) {
            $point = isset($pointData['id_point'])
                ? KpiPoint::findOrFail($pointData['id_point'])
                : new KpiPoint(['kpis_id_kpi' => $kpi->id_kpi]);

            $point->nama = $pointData['nama'];
            $point->bobot = $pointData['bobot'];
            $point->kpis_id_kpi = $kpi->id_kpi;
            $point->save();

            // pertanyaan
            foreach ($pointData['questions'] as $qData) {
                $pertanyaan = is_array($qData) ? $qData['pertanyaan'] : $qData;

                $question = isset($qData['id_question'])
                    ? KpiQuestion::findOrFail($qData['id_question'])
                    : new KpiQuestion(['kpi_point_id' => $point->id_point]);

                $question->pertanyaan = $pertanyaan;
                $question->kpi_point_id = $point->id_point;
                $question->save();
            }
        }

        // relasi division
        if ($isGlobal) {
            $allDivisions = DB::table('divisions')->pluck('id_divisi');
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
    }

    /**
     * List KPI berdasarkan divisi (global + divisi spesifik)
     */
    public function listKpiByDivision($divisionId)
    {
        $divisionExists = DB::table('divisions')->where('id_divisi', $divisionId)->exists();
        if (!$divisionExists) {
            return response()->json(['message' => 'Divisi tidak ditemukan'], 404);
        }

        $globalKpis = Kpi::where('is_global', true)
            ->with(['points.questions' => function ($query) {
                $query->orderBy('id_question');
            }])
            ->orderBy('id_kpi')
            ->get();

        $divisionKpis = Kpi::whereHas('divisions', function ($q) use ($divisionId) {
            $q->where('divisions.id_divisi', $divisionId);
        })
            ->with(['points.questions' => function ($query) {
                $query->orderBy('id_question');
            }])
            ->orderBy('id_kpi')
            ->get();

        $allKpis = $globalKpis->concat($divisionKpis)
            ->unique('id_kpi')
            ->values()
            ->map(function ($kpi) {
                return [
                    'id' => $kpi->id_kpi,
                    'id_kpi' => $kpi->id_kpi,
                    'nama' => $kpi->nama,
                    'bobot' => $kpi->bobot,
                    'is_global' => $kpi->is_global,
                    'points' => $kpi->points->map(function ($point) {
                        return [
                            'id' => $point->id_point,
                            'id_point' => $point->id_point,
                            'nama' => $point->nama,
                            'bobot' => $point->bobot,
                            'questions' => $point->questions->map(function ($question) {
                                return [
                                    'id' => $question->id_question,
                                    'id_question' => $question->id_question,
                                    'pertanyaan' => $question->pertanyaan
                                ];
                            })
                        ];
                    })
                ];
            });

        return response()->json($allKpis);
    }

    /**
     * List KPI global
     */
    public function listGlobalKpi()
    {
        $kpis = Kpi::where('is_global', true)->with('points.questions')->get();
        return response()->json(['kpis' => $kpis]);
    }

    /**
     * Delete KPI global
     */
    public function deleteGlobalKpi($id)
    {
        $kpi = Kpi::where('is_global', true)->findOrFail($id);
        $kpi->delete();
        return response()->json(['message' => 'KPI global berhasil dihapus']);
    }

    /**
     * Update KPI global
     */
    public function updateGlobalKpi(Request $request, $id)
    {
        $kpi = Kpi::where('is_global', true)->findOrFail($id);
        $kpi->update($request->only(['nama', 'deskripsi', 'bobot']));
        return response()->json(['message' => 'KPI global berhasil diperbarui']);
    }

    /**
     * Delete KPI divisi
     */
    public function deleteDivisionKpi($divisionId, $kpiId)
    {
        $exists = DB::table('division_has_kpis')
            ->where('id_divisi', $divisionId)
            ->where('kpis_id_kpi', $kpiId)
            ->exists();

        if (!$exists) {
            return response()->json(['message' => 'KPI tidak ditemukan'], 404);
        }

        DB::table('division_has_kpis')
            ->where('id_divisi', $divisionId)
            ->where('kpis_id_kpi', $kpiId)
            ->delete();

        $stillUsed = DB::table('division_has_kpis')->where('kpis_id_kpi', $kpiId)->exists();
        if (!$stillUsed) {
            Kpi::find($kpiId)?->delete();
        }

        return response()->json(['message' => 'KPI divisi berhasil dihapus']);
    }

    /**
     * Update KPI divisi
     */
    public function updateDivisionKpi(Request $request, $divisionId, $kpiId)
    {
        $exists = DB::table('division_has_kpis')
            ->where('id_divisi', $divisionId)
            ->where('kpis_id_kpi', $kpiId)
            ->exists();

        if (!$exists) {
            return response()->json(['message' => 'KPI tidak ditemukan'], 404);
        }

        $kpi = Kpi::findOrFail($kpiId);
        $kpi->update($request->only(['nama', 'deskripsi', 'bobot']));

        return response()->json(['message' => 'KPI divisi berhasil diperbarui']);
    }

    /**
     * Ambil KPI beserta nilai tiap karyawan (global + divisi)
     */
    public function getDivisionKpi($divisionId, $tahun, $bulan)
    {
        $employees = Employee::where('divisions_id_divisi', $divisionId)->get();
        if ($employees->isEmpty()) {
            return response()->json(['message' => 'Tidak ada karyawan di divisi ini'], 404);
        }

        $globalKpis = Kpi::where('is_global', true)->with('points.questions')->get();
        $divisionKpis = Kpi::whereHas('divisions', function ($q) use ($divisionId) {
            $q->where('divisions.id_divisi', $divisionId);
        })->with('points.questions')->get();

        $kpis = $globalKpis->concat($divisionKpis)->unique('id_kpi')->values();

        $result = [];
        foreach ($employees as $employee) {
            $employeeData = [
                'employee_id' => $employee->id_karyawan,
                'nama_karyawan' => $employee->nama_karyawan,
                'kpis' => []
            ];

            foreach ($kpis as $kpi) {
                $nilaiAkhir = KpiHasEmployee::where('kpis_id_kpi', $kpi->id_kpi)
                    ->where('employees_id_karyawan', $employee->id_karyawan)
                    ->where('tahun', $tahun)
                    ->where('bulan', $bulan)
                    ->value('nilai_akhir') ?? 0;

                $points = [];
                foreach ($kpi->points as $point) {
                    $questions = [];
                    foreach ($point->questions as $q) {
                        $nilai = KpiQuestionHasEmployee::where('kpi_question_id_question', $q->id_question)
                            ->where('employees_id_karyawan', $employee->id_karyawan)
                            ->value('nilai') ?? null;

                        $questions[] = [
                            'id_question' => $q->id_question,
                            'pertanyaan' => $q->pertanyaan,
                            'nilai' => $nilai
                        ];
                    }

                    $points[] = [
                        'id_point' => $point->id_point,
                        'nama' => $point->nama,
                        'bobot' => $point->bobot,
                        'questions' => $questions
                    ];
                }

                $employeeData['kpis'][] = [
                    'id_kpi' => $kpi->id_kpi,
                    'nama' => $kpi->nama,
                    'bobot' => $kpi->bobot,
                    'nilai_akhir' => $nilaiAkhir,
                    'points' => $points
                ];
            }

            $result[] = $employeeData;
        }

        return response()->json([
            'id_divisi' => $divisionId,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'employees' => $result
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
        $kpi->delete();
        return response()->json(['message' => 'KPI berhasil dihapus']);
    }

    /**
     * Simpan/update nilai karyawan per pertanyaan
     */
    public function storeEmployeeScore(Request $request)
    {
        $validated = $request->validate([
            'id_karyawan' => 'required|exists:employees,id_karyawan',
            'hasil' => 'required|array|min:1',
            'hasil.*.jawaban' => 'required|array|min:1',
            'hasil.*.jawaban.*.id' => 'required|exists:kpi_questions,id_question',
            'hasil.*.jawaban.*.jawaban' => 'required|integer|min:1|max:4',
        ]);

        foreach ($validated['hasil'] as $aspek) {
            foreach ($aspek['jawaban'] as $jawaban) {
                KpiQuestionHasEmployee::updateOrCreate(
                    [
                        'employees_id_karyawan' => $validated['id_karyawan'],
                        'kpi_question_id_question' => $jawaban['id']
                    ],
                    ['nilai' => $jawaban['jawaban']]
                );
            }
        }

        return response()->json([
            'message' => 'Nilai karyawan berhasil disimpan'
        ]);
    }

    /**
     * Hitung nilai akhir KPI per karyawan (termasuk absensi)
     */
    public function calculateFinalScore($employeeId, $tahun, $bulan)
    {
        $employee = Employee::findOrFail($employeeId);
        $kpis = Kpi::with('points.questions')->get();

        foreach ($kpis as $kpi) {
            $nilaiAkhir = 0;

            foreach ($kpi->points as $point) {
                $total = $point->questions->sum(function ($q) use ($employeeId, $tahun, $bulan) {
                    return KpiQuestionHasEmployee::where('kpi_question_id_question', $q->id_question)
                        ->where('employees_id_karyawan', $employeeId)
                        ->whereYear('created_at', $tahun)   // filter tahun
                        ->whereMonth('created_at', $bulan)  // filter bulan
                        ->value('nilai') ?? 0;
                });

                $count = $point->questions->count();
                $avg = $count > 0 ? $total / $count : 0;
                $skorSubAspek = ($avg * 2.5) * ($point->bobot / 100);
                $nilaiAkhir += $skorSubAspek;
            }

            // Integrasi Absensi
            $attendances = Attendance::where('employee_id', $employeeId)
                ->whereYear('date', $tahun)
                ->whereMonth('date', $bulan)
                ->get();

            if ($attendances->count() > 0) {
                $totalHari = $attendances->count();
                $hadir = $attendances->where('status', 'Present at workday (PW)')->count();
                $sakit = $attendances->where('status', 'Sick (S)')->count();
                $izin  = $attendances->where('status', 'Permission (I)')->count();
                $alpa  = $attendances->where('status', 'Absent (A)')->count();

                $skorAbsensi = max(0, ($hadir / $totalHari) * 100
                    - ($alpa * 25)
                    - ($sakit * 10)
                    - ($izin * 10));

                $bobotAbsensi = 20;
                $nilaiAkhir += ($skorAbsensi * ($bobotAbsensi / 100));
            }

            // Simpan nilai akhir
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

        return response()->json(['message' => 'Nilai akhir KPI berhasil dihitung (termasuk absensi)']);
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

// Untuk kepala divisi: ambil karyawan di divisinya
public function getEmployeesByDivision($divisionId)
{
    $employees = Employee::whereHas('roles.division', function ($q) use ($divisionId) {
        $q->where('id_divisi', $divisionId);
    })
    ->with('roles.division')
    ->get();

    return response()->json($employees);
}

public function getkepalaDivisionEmployees()
{
    $user = Auth::user();
    $divisionId = optional(optional($user->roles->first())->division)->id_divisi;

    return $this->getEmployeesByDivision($divisionId);
}


}
