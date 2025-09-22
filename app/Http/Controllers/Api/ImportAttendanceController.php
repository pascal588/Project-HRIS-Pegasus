<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\Employee;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportAttendanceController extends Controller
{<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kpi;
use App\Models\KpiPoint;
use App\Models\KpiQuestion;
use App\Models\KpiQuestionHasEmployee;
use App\Models\KpiPointsHasEmployee;
use App\Models\KpiHasEmployee;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Period;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class KpiController extends Controller
{
    // GET /api/kpis - Get all KPIs
    public function getAllKpis()
    {
        try {
            $kpis = Kpi::with(['points.questions', 'period'])->get();
            
            return response()->json([
                'success' => true,
                'data' => $kpis
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch KPIs: ' . $e->getMessage()
            ], 500);
        }
    }

    // POST /api/kpis - Store KPI
    public function storeKpi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'periode_id' => 'required|exists:periods,id_periode',
            'is_global' => 'required|boolean',
            'division_id' => 'nullable|integer|exists:divisions,id_divisi',
            'kpis' => 'required|array|min:1',
            'kpis.*.nama' => 'required|string|max:100',
            'kpis.*.deskripsi' => 'nullable|string',
            'kpis.*.bobot' => 'required|numeric|min:0|max:100',
            'kpis.*.points' => 'required|array|min:1',
            'kpis.*.points.*.nama' => 'required|string|max:255',
            'kpis.*.points.*.bobot' => 'required|numeric|min:0|max:100',
            'kpis.*.points.*.questions' => 'required|array|min:1',
            'kpis.*.points.*.questions.*.pertanyaan' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->kpis as $kpiData) {
                $this->saveSingleKpi($kpiData, $request->is_global, $request->division_id, $request->periode_id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'All KPIs saved successfully!'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save KPIs: ' . $e->getMessage()
            ], 500);
        }
    }

    private function saveSingleKpi(array $kpiData, bool $isGlobal, $divisionId = null, $periodeId = null)
    {
        $kpi = isset($kpiData['id_kpi'])
            ? Kpi::findOrFail($kpiData['id_kpi'])
            : new Kpi();

        $kpi->nama = $kpiData['nama'];
        $kpi->deskripsi = $kpiData['deskripsi'] ?? null;
        $kpi->bobot = $kpiData['bobot'];
        $kpi->is_global = $isGlobal;
        $kpi->periode_id = $periodeId;
        $kpi->save();

        foreach ($kpiData['points'] as $pointData) {
            $point = isset($pointData['id_point'])
                ? KpiPoint::findOrFail($pointData['id_point'])
                : new KpiPoint();
            
            $point->kpis_id_kpi = $kpi->id_kpi;
            $point->nama = $pointData['nama'];
            $point->bobot = $pointData['bobot'];
            $point->save();

            foreach ($pointData['questions'] as $qData) {
                $pertanyaan = is_array($qData) ? $qData['pertanyaan'] : $qData;
                $question = isset($qData['id_question'])
                    ? KpiQuestion::findOrFail($qData['id_question'])
                    : new KpiQuestion();
                
                $question->kpi_point_id = $point->id_point;
                $question->pertanyaan = $pertanyaan;
                $question->save();
            }
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
    }

    // GET /api/kpis/division/{divisionId} - List KPI by division
    public function listKpiByDivision($divisionId)
    {
        try {
            $division = Division::find($divisionId);
            if (!$division) {
                return response()->json([
                    'success' => false,
                    'message' => 'Division not found'
                ], 404);
            }

            $globalKpis = Kpi::where('is_global', true)
                ->with(['points.questions' => fn($q) => $q->orderBy('id_question')])
                ->orderBy('id_kpi')
                ->get();

            $divisionKpis = Kpi::whereHas('divisions', fn($q) => $q->where('divisions.id_divisi', $divisionId))
                ->with(['points.questions' => fn($q) => $q->orderBy('id_question')])
                ->orderBy('id_kpi')
                ->get();

            $allKpis = $globalKpis->concat($divisionKpis)->unique('id_kpi')->values();

            return response()->json([
                'success' => true,
                'data' => $allKpis
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch KPIs: ' . $e->getMessage()
            ], 500);
        }
    }

    // GET /api/kpis/global - List global KPIs
    public function listGlobalKpi()
    {
        try {
            $kpis = Kpi::where('is_global', true)
                ->with('points.questions')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $kpis
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch global KPIs: ' . $e->getMessage()
            ], 500);
        }
    }

    // GET /api/kpis/current-period - Get current active period for editing
    public function getCurrentPeriod()
    {
        try {
            $currentPeriod = Period::where('status', 'active')
                ->where('attendance_uploaded', false)
                ->first();

            if (!$currentPeriod) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active period available for editing'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $currentPeriod
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch current period: ' . $e->getMessage()
            ], 500);
        }
    }

    // GET /api/kpis/period/{periodId}/division/{divisionId} - Get KPI for specific period and division
    public function getKpiByPeriodAndDivision($periodId, $divisionId)
    {
        try {
            $division = Division::find($divisionId);
            $period = Period::find($periodId);

            if (!$division || !$period) {
                return response()->json([
                    'success' => false,
                    'message' => 'Division or period not found'
                ], 404);
            }

            $globalKpis = Kpi::where('is_global', true)
                ->where('periode_id', $periodId)
                ->with('points.questions')
                ->get();

            $divisionKpis = Kpi::whereHas('divisions', fn($q) => $q->where('divisions.id_divisi', $divisionId))
                ->where('periode_id', $periodId)
                ->with('points.questions')
                ->get();

            $allKpis = $globalKpis->concat($divisionKpis)->unique('id_kpi')->values();

            return response()->json([
                'success' => true,
                'data' => $allKpis
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch KPIs: ' . $e->getMessage()
            ], 500);
        }
    }

    // DELETE /api/kpis/global/{id} - Delete global KPI
    public function deleteGlobalKpi($id)
    {
        try {
            $kpi = Kpi::where('is_global', true)->findOrFail($id);
            $kpi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Global KPI deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete global KPI: ' . $e->getMessage()
            ], 500);
        }
    }

    // PUT /api/kpis/global/{id} - Update global KPI
    public function updateGlobalKpi(Request $request, $id)
    {
        try {
            $kpi = Kpi::where('is_global', true)->findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'nama' => 'sometimes|string|max:100',
                'deskripsi' => 'nullable|string',
                'bobot' => 'sometimes|numeric|min:0|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $kpi->update($request->only(['nama', 'deskripsi', 'bobot']));

            return response()->json([
                'success' => true,
                'message' => 'Global KPI updated successfully',
                'data' => $kpi
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update global KPI: ' . $e->getMessage()
            ], 500);
        }
    }

    // GET /api/kpis/division/{divisionId}/{periodeId} - Get division KPI for period
    public function getDivisionKpi($divisionId, $periodeId)
    {
        try {
            $division = Division::find($divisionId);
            $period = Period::find($periodeId);

            if (!$division || !$period) {
                return response()->json([
                    'success' => false,
                    'message' => 'Division or period not found'
                ], 404);
            }

            $employees = Employee::where('divisions_id_divisi', $divisionId)->get();
            if ($employees->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No employees in this division'
                ], 404);
            }

            $globalKpis = Kpi::where('is_global', true)
                ->where('periode_id', $periodeId)
                ->with('points.questions')
                ->get();

            $divisionKpis = Kpi::whereHas('divisions', fn($q) => $q->where('divisions.id_divisi', $divisionId))
                ->where('periode_id', $periodeId)
                ->with('points.questions')
                ->get();

            $kpis = $globalKpis->concat($divisionKpis)->unique('id_kpi')->values();
            $result = [];

            foreach ($employees as $employee) {
                $employeeData = [
                    'employee_id' => $employee->id_karyawan,
                    'nama' => $employee->nama,
                    'kpis' => []
                ];

                foreach ($kpis as $kpi) {
                    $evaluation = KpiHasEmployee::where('kpis_id_kpi', $kpi->id_kpi)
                        ->where('employees_id_karyawan', $employee->id_karyawan)
                        ->where('periode_id', $periodeId)
                        ->first();

                    $points = [];
                    foreach ($kpi->points as $point) {
                        $questions = [];
                        foreach ($point->questions as $q) {
                            $answer = KpiQuestionHasEmployee::where('kpi_question_id_question', $q->id_question)
                                ->where('employees_id_karyawan', $employee->id_karyawan)
                                ->where('periode_id', $periodeId)
                                ->first();

                            $questions[] = [
                                'id_question' => $q->id_question,
                                'pertanyaan' => $q->pertanyaan,
                                'nilai' => $answer->nilai ?? null
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
                        'nilai_akhir' => $evaluation->nilai_akhir ?? 0,
                        'points' => $points
                    ];
                }
                $result[] = $employeeData;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'division' => $division->nama_divisi,
                    'period' => $period->nama,
                    'employees' => $result
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch division KPI: ' . $e->getMessage()
            ], 500);
        }
    }

    // POST /api/kpis/employee-score - Store employee score
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
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $period = Period::find($request->periode_id);
            
            // ✅ VALIDASI PERIODE BISA DIINPUT
            if (!$period->canBeEvaluated()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot input scores for this period. Status: ' . $period->status
                ], 400);
            }

            DB::beginTransaction();

            foreach ($request->hasil as $aspek) {
                foreach ($aspek['jawaban'] as $jawaban) {
                    KpiQuestionHasEmployee::updateOrCreate(
                        [
                            'employees_id_karyawan' => $request->id_karyawan,
                            'kpi_question_id_question' => $jawaban['id'],
                            'periode_id' => $request->periode_id
                        ],
                        ['nilai' => $jawaban['jawaban']]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Employee scores saved successfully!'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save scores: ' . $e->getMessage()
            ], 500);
        }
    }

    // GET /api/kpis/attendance-summary/{employeeId}/{periodeId} - Get attendance summary for KPI
    public function getAttendanceSummary($employeeId, $periodeId)
    {
        try {
            $employee = Employee::findOrFail($employeeId);
            $period = Period::findOrFail($periodeId);

            $attendances = Attendance::where('employee_id', $employeeId)
                ->where('periode_id', $periodeId)
                ->get();

            $summary = [
                'total_days' => $attendances->count(),
                'hadir' => $attendances->where('status', 'Present at workday (PW)')->count(),
                'izin' => $attendances->where('status', 'Permission (I)')->count(),
                'sakit' => $attendances->where('status', 'Sick (S)')->count(),
                'mangkir' => $attendances->where('status', 'Absent (A)')->count(),
                'terlambat' => $attendances->sum('late'),
                'jumlah_terlambat' => $attendances->where('late', '>', 0)->count(),
                'early_leave' => $attendances->sum('early_leave'),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'employee' => $employee->nama,
                    'period' => $period->nama,
                    'attendance_summary' => $summary
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance summary: ' . $e->getMessage()
            ], 500);
        }
    }

    // POST /api/kpis/calculate/{employeeId}/{periodeId} - Calculate final score
    public function calculateFinalScore($employeeId, $periodeId)
    {
        try {
            $employee = Employee::findOrFail($employeeId);
            $period = Period::findOrFail($periodeId);

            // ✅ VALIDASI PERIODE BISA DIKALKULASI
            if (!$period->canBeEvaluated()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot calculate scores for this period. Status: ' . $period->status
                ], 400);
            }

            $tahun = $period->tanggal_mulai->format('Y');
            $bulan = $period->tanggal_mulai->format('m');

            $kpis = Kpi::where('periode_id', $periodeId)->with('points.questions')->get();

            DB::beginTransaction();

            foreach ($kpis as $kpi) {
                $kpiHasEmployee = KpiHasEmployee::firstOrCreate(
                    [
                        'kpis_id_kpi' => $kpi->id_kpi,
                        'employees_id_karyawan' => $employeeId,
                        'periode_id' => $periodeId
                    ],
                    [
                        'tahun' => $tahun,
                        'bulan' => $bulan
                    ]
                );

                $totalKpiScore = 0;
                foreach ($kpi->points as $point) {
                    $total = $point->questions->sum(function($q) use ($employeeId, $periodeId) {
                        return KpiQuestionHasEmployee::where('kpi_question_id_question', $q->id_question)
                            ->where('employees_id_karyawan', $employeeId)
                            ->where('periode_id', $periodeId)
                            ->value('nilai') ?? 0;
                    });

                    $count = $point->questions->count();
                    $avg = $count > 0 ? $total / $count : 0;
                    $skorSubAspek = ($avg * 2.5) * ($point->bobot / 100);
                    $totalKpiScore += $skorSubAspek;

                    KpiPointsHasEmployee::updateOrCreate(
                        [
                            'kpis_has_employee_id' => $kpiHasEmployee->id,
                            'kpi_point_id' => $point->id_point
                        ],
                        ['nilai' => $skorSubAspek]
                    );
                }

                // ✅ TAMBAH IMMUTABILITY
                $kpiHasEmployee->update([
                    'nilai_akhir' => $totalKpiScore,
                    'is_finalized' => true,
                    'finalized_at' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Final scores calculated successfully!',
                'data' => [
                    'employee' => $employee->nama,
                    'period' => $period->nama
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate scores: ' . $e->getMessage()
            ], 500);
        }
    }

    // GET /api/kpis/scores/{employeeId}/{periodeId} - Get scores by main aspect
    public function getScoreByAspekUtama($employeeId, $periodeId)
    {
        try {
            $employee = Employee::findOrFail($employeeId);
            $period = Period::findOrFail($periodeId);

            $kpis = Kpi::where('periode_id', $periodeId)->with('points')->get();
            $result = [];

            foreach ($kpis as $kpi) {
                $evaluation = KpiHasEmployee::where('kpis_id_kpi', $kpi->id_kpi)
                    ->where('employees_id_karyawan', $employeeId)
                    ->where('periode_id', $periodeId)
                    ->first();

                $result[] = [
                    'nama' => $kpi->nama,
                    'bobot' => $kpi->bobot,
                    'nilai' => $evaluation->nilai_akhir ?? 0,
                    'is_finalized' => $evaluation->is_finalized ?? false
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'employee' => $employee->nama,
                    'period' => $period->nama,
                    'scores' => $result
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch scores: ' . $e->getMessage()
            ], 500);
        }
    }

    // PUT /api/kpis/{id} - Update KPI
    public function updateKpi(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $kpi = Kpi::findOrFail($id);
            $kpi->update($request->only(['nama', 'deskripsi', 'bobot', 'is_global']));

            foreach ($request->points as $pointData) {
                $point = isset($pointData['id_point'])
                    ? KpiPoint::findOrFail($pointData['id_point'])
                    : new KpiPoint();
                
                $point->kpis_id_kpi = $kpi->id_kpi;
                $point->nama = $pointData['nama'];
                $point->bobot = $pointData['bobot'];
                $point->save();

                foreach ($pointData['questions'] as $qData) {
                    $question = isset($qData['id_question'])
                        ? KpiQuestion::findOrFail($qData['id_question'])
                        : new KpiQuestion();
                    
                    $question->kpi_point_id = $point->id_point;
                    $question->pertanyaan = $qData['pertanyaan'];
                    $question->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'KPI updated successfully!'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update KPI: ' . $e->getMessage()
            ], 500);
        }
    }

    // DELETE /api/kpis/{id} - Delete KPI
    public function deleteKpi($id)
    {
        try {
            $kpi = Kpi::findOrFail($id);
            $kpi->delete();

            return response()->json([
                'success' => true,
                'message' => 'KPI deleted successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete KPI: ' . $e->getMessage()
            ], 500);
        }
    }
}

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathName());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Ekstrak metadata
            $metadata = $this->extractMetadata($rows);
            
            // Ekstrak data absensi
            $attendanceData = $this->extractAttendanceData($rows);
            
            // Simpan ke database
            $result = $this->saveAttendanceData($metadata, $attendanceData);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diimport',
                'data' => [
                    'period' => $metadata['period'],
                    'employee_id' => $metadata['employee_id'],
                    'employee_name' => $metadata['employee_name'],
                    'imported_count' => $result['imported'],
                    'skipped_count' => $result['skipped']
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimport data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function extractMetadata($rows)
    {
        $metadata = [
            'period' => '',
            'employee_id' => '',
            'employee_name' => ''
        ];

        foreach ($rows as $row) {
            if (isset($row[1])) {
                if (strpos($row[1], 'Period') !== false && isset($row[2])) {
                    $metadata['period'] = $row[2];
                } elseif (strpos($row[1], 'Personnel ID') !== false && isset($row[2])) {
                    $metadata['employee_id'] = $row[2];
                } elseif (strpos($row[1], 'Personnel name') !== false && isset($row[2])) {
                    $metadata['employee_name'] = $row[2];
                }
            }
        }

        return $metadata;
    }

    private function extractAttendanceData($rows)
    {
        $data = [];
        $headers = [];
        $startCollecting = false;

        foreach ($rows as $index => $row) {
            // Cari baris header
            if (isset($row[1]) && $row[1] === 'Date') {
                $headers = $row;
                $startCollecting = true;
                continue;
            }

            if ($startCollecting && !empty($row[1]) && is_numeric($row[0])) {
                $rowData = [];
                foreach ($headers as $colIndex => $header) {
                    if (isset($row[$colIndex])) {
                        $rowData[$header] = $row[$colIndex];
                    }
                }
                $data[] = $rowData;
            }
        }

        return $data;
    }

    private function saveAttendanceData($metadata, $attendanceData)
    {
        $imported = 0;
        $skipped = 0;

        // Parse period
        $periodParts = explode(' - ', $metadata['period']);
        $startPeriod = Carbon::parse($periodParts[0]);
        $endPeriod = Carbon::parse($periodParts[1]);

        DB::beginTransaction();

        try {
            foreach ($attendanceData as $data) {
                if (empty($data['Date']) || $data['Status'] === 'Non-working day (NW)') {
                    $skipped++;
                    continue;
                }

                // Cari employee berdasarkan ID
                $employee = Employee::where('id_karyawan', $metadata['employee_id'])->first();
                
                if (!$employee) {
                    $skipped++;
                    continue;
                }

                // Parse tanggal
                $date = Carbon::parse($data['Date']);

                // Konversi status
                $statusMap = [
                    'Present at workday (PW)' => 'Hadir',
                    'Sick (S)' => 'Sakit',
                    'Permission (I)' => 'Izin',
                    'Absent (A)' => 'Mangkir'
                ];

                $status = $statusMap[$data['Status']] ?? 'Mangkir';

                // Hitung lama kerja jika ada
                $workDuration = null;
                if (!empty($data['Attendance']) && $data['Attendance'] !== '-') {
                    $timeParts = explode(':', $data['Attendance']);
                    $workDuration = ($timeParts[0] * 60) + $timeParts[1]; // dalam menit
                }

                // Simpan atau update data absensi
                Absen::updateOrCreate(
                    [
                        'employees_id_karyawan' => $employee->id_karyawan,
                        'created_at' => $date->format('Y-m-d')
                    ],
                    [
                        'jam_masuk' => !empty($data['Clock-in']) && $data['Clock-in'] !== '-' ? $data['Clock-in'] : null,
                        'jam_keluar' => !empty($data['Clock-out']) && $data['Clock-out'] !== '-' ? $data['Clock-out'] : null,
                        'status' => $status,
                        'lama_kerja' => $workDuration
                    ]
                );

                $imported++;
            }

            DB::commit();
            return ['imported' => $imported, 'skipped' => $skipped];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getImportedPeriods()
    {
        $periods = Absen::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function($item) {
                $monthName = Carbon::create()->month($item->month)->format('F');
                return [
                    'year' => $item->year,
                    'month' => $item->month,
                    'month_name' => $monthName,
                    'count' => $item->count
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $periods
        ]);
    }

    public function getAttendanceByPeriod(Request $request, $year, $month)
    {
        $employeeId = $request->query('employee_id');
        
        $query = Absen::with('employee')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month);
            
        if ($employeeId) {
            $query->where('employees_id_karyawan', $employeeId);
        }
        
        $attendance = $query->orderBy('created_at')->get();
        
        // Hitung ringkasan
        $summary = [
            'hadir' => $attendance->where('status', 'Hadir')->count(),
            'sakit' => $attendance->where('status', 'Sakit')->count(),
            'izin' => $attendance->where('status', 'Izin')->count(),
            'mangkir' => $attendance->where('status', 'Mangkir')->count(),
            'terlambat' => 0 // Anda perlu menambahkan logika untuk menghitung keterlambatan
        ];
        
        return response()->json([
            'success' => true,
            'data' => [
                'attendance' => $attendance,
                'summary' => $summary
            ]
        ]);
    }
}