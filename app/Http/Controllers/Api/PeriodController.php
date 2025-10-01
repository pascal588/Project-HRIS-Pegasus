<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Period;
use App\Models\Attendance;
use App\Models\Kpi;
use App\Models\KpiHasEmployee;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PeriodController extends Controller
{
    public function index(Request $request)
    {
        $query = Period::withCount(['attendances', 'kpis', 'kpiEvaluations']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // ✅ FILTER BARU: Hanya periode dengan KPI published
        if ($request->has('kpi_published')) {
            $query->where('kpi_published', $request->boolean('kpi_published'));
        }

        // Filter lainnya tetap...
        if ($request->has('start_date')) {
            $query->where('tanggal_mulai', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('tanggal_selesai', '<=', $request->end_date);
        }

        if ($request->has('attendance_uploaded')) {
            $query->where('attendance_uploaded', $request->attendance_uploaded);
        }

        $periods = $query->orderBy('tanggal_mulai', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $periods
        ], 200);
    }

    // GET /api/periods/{id} - Detail periode lengkap
    public function show($id)
    {
        try {
            $period = Period::with([
                'attendances.employee',
                'kpis.points.questions',
                'kpiEvaluations.employee',
                'kpiEvaluations.kpi'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $period
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Periode tidak ditemukan'
            ], 404);
        }
    }

    // POST /api/periods - Buat periode baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'evaluation_start_date' => 'nullable|date|after:tanggal_selesai',
            'evaluation_end_date' => 'nullable|date|after:evaluation_start_date',
            'editing_start_date' => 'nullable|date|after:evaluation_end_date',
            'editing_end_date' => 'nullable|date|after:editing_start_date',
            'status' => 'required|in:draft,active,locked'
        ]);

        // Cek apakah ada periode yang overlap
        $overlap = Period::where(function ($q) use ($validated) {
            $q->whereBetween('tanggal_mulai', [$validated['tanggal_mulai'], $validated['tanggal_selesai']])
                ->orWhereBetween('tanggal_selesai', [$validated['tanggal_mulai'], $validated['tanggal_selesai']])
                ->orWhere(function ($q) use ($validated) {
                    $q->where('tanggal_mulai', '<=', $validated['tanggal_mulai'])
                        ->where('tanggal_selesai', '>=', $validated['tanggal_selesai']);
                });
        })->exists();

        if ($overlap) {
            return response()->json([
                'success' => false,
                'message' => 'Periode ini overlap dengan periode yang sudah ada'
            ], 422);
        }

        $period = Period::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Periode berhasil dibuat',
            'data' => $period
        ], 201);
    }

    // PUT /api/periods/{id} - Update periode
    public function update(Request $request, $id)
    {
        try {
            $period = Period::findOrFail($id);

            // Tidak bisa update periode yang sudah locked
            if ($period->status === 'locked') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa mengupdate periode yang sudah dikunci'
                ], 422);
            }

            $validated = $request->validate([
                'nama' => 'sometimes|string|max:255',
                'tanggal_mulai' => 'sometimes|date',
                'tanggal_selesai' => 'sometimes|date|after:tanggal_mulai',
                'evaluation_start_date' => 'nullable|date|after:tanggal_selesai',
                'evaluation_end_date' => 'nullable|date|after:evaluation_start_date',
                'editing_start_date' => 'nullable|date|after:evaluation_end_date',
                'editing_end_date' => 'nullable|date|after:editing_start_date',
                'status' => 'sometimes|in:draft,active,locked'
            ]);

            $period->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Periode berhasil diperbarui',
                'data' => $period
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate periode: ' . $e->getMessage()
            ], 500);
        }
    }

    // DELETE /api/periods/{id} - Hapus periode
    public function destroy($id)
    {
        try {
            $period = Period::findOrFail($id);

            // Tidak bisa hapus periode yang sudah ada datanya
            if ($period->attendances()->exists() || $period->kpiEvaluations()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa menghapus periode yang sudah memiliki data absensi atau evaluasi KPI'
                ], 422);
            }

            $period->delete();

            return response()->json([
                'success' => true,
                'message' => 'Periode berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus periode: ' . $e->getMessage()
            ], 500);
        }
    }

    // POST /api/periods/{id}/mark-attendance-uploaded - Tandai attendance sudah diupload
    public function markAttendanceUploaded($id)
    {
        try {
            $period = Period::findOrFail($id);

            $period->update([
                'attendance_uploaded' => true,
                'attendance_uploaded_at' => now(),
                'status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status attendance diupload untuk periode ' . $period->nama,
                'data' => $period
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update status attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    // POST /api/periods/{id}/lock - Kunci periode
    public function lockPeriod($id)
    {
        try {
            $period = Period::findOrFail($id);

            if ($period->status === 'locked') {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode sudah dikunci'
                ], 422);
            }

            $period->update([
                'status' => 'locked',
                'editing_end_date' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Periode ' . $period->nama . ' berhasil dikunci',
                'data' => $period
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunci periode: ' . $e->getMessage()
            ], 500);
        }
    }

    // POST /api/periods/{id}/unlock - Buka kunci periode
    public function unlockPeriod($id)
    {
        try {
            $period = Period::findOrFail($id);

            if ($period->status !== 'locked') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya periode yang terkunci yang bisa dibuka'
                ], 422);
            }

            $period->update([
                'status' => 'active',
                'editing_end_date' => Carbon::now()->addDays(7)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Periode ' . $period->nama . ' berhasil dibuka',
                'data' => $period
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuka kunci periode: ' . $e->getMessage()
            ], 500);
        }
    }

    // GET /api/periods/{id}/evaluation-status - Status evaluasi KPI
    public function getEvaluationStatus($id)
    {
        try {
            $period = Period::findOrFail($id);

            $totalEmployees = Employee::where('status', 'Aktif')->count();
            $evaluatedEmployees = KpiHasEmployee::where('periode_id', $id)
                ->distinct('employees_id_karyawan')
                ->count();

            $evaluationProgress = $totalEmployees > 0 ? ($evaluatedEmployees / $totalEmployees) * 100 : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'periode' => $period->nama,
                    'status' => $period->status,
                    'total_karyawan' => $totalEmployees,
                    'karyawan_terevaluasi' => $evaluatedEmployees,
                    'progress_evaluasi' => round($evaluationProgress, 2),
                    'bisa_dikunci' => $evaluatedEmployees >= $totalEmployees && $period->status === 'active'
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status evaluasi: ' . $e->getMessage()
            ], 500);
        }
    }

    // GET /api/periods/{id}/attendance-summary - Summary absensi
    public function getAttendanceSummary($id)
    {
        try {
            $period = Period::findOrFail($id);

            $attendances = Attendance::where('periode_id', $id)
                ->with('employee')
                ->get();

            $summary = [
                'total_record_absensi' => $attendances->count(),
                'jumlah_hadir' => $attendances->where('status', 'Present at workday (PW)')->count(),
                'jumlah_absen' => $attendances->where('status', 'Absent (A)')->count(),
                'jumlah_sakit' => $attendances->where('status', 'Sick (S)')->count(),
                'jumlah_izin' => $attendances->where('status', 'Permission (I)')->count(),
                'jumlah_non_kerja' => $attendances->where('status', 'Non-working day (NW)')->count(),
                'total_keterlambatan' => $attendances->sum('late'),
                'total_pulang_cepat' => $attendances->sum('early_leave'),
                'persentase_kehadiran' => $attendances->count() > 0 ?
                    ($attendances->where('status', 'Present at workday (PW)')->count() / $attendances->count()) * 100 : 0
            ];

            return response()->json([
                'success' => true,
                'data' => $summary
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil summary absensi: ' . $e->getMessage()
            ], 500);
        }
    }

    // GET /api/periods/{id}/kpi-summary - Summary nilai KPI
    public function getKpiSummary($id)
    {
        try {
            $period = Period::findOrFail($id);

            $kpiEvaluations = KpiHasEmployee::where('periode_id', $id)
                ->with(['employee', 'kpi'])
                ->get();

            $summary = [
                'total_evaluasi_kpi' => $kpiEvaluations->count(),
                'nilai_rata_rata' => $kpiEvaluations->avg('nilai_akhir'),
                'nilai_tertinggi' => $kpiEvaluations->max('nilai_akhir'),
                'nilai_terendah' => $kpiEvaluations->min('nilai_akhir'),
                'karyawan_terevaluasi' => $kpiEvaluations->unique('employees_id_karyawan')->count(),
                'distribusi_kpi' => $kpiEvaluations->groupBy('kpis_id_kpi')->map(function ($group) {
                    return [
                        'nama_kpi' => $group->first()->kpi->nama,
                        'nilai_rata_rata' => $group->avg('nilai_akhir'),
                        'jumlah' => $group->count()
                    ];
                })->values()
            ];

            return response()->json([
                'success' => true,
                'data' => $summary
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil summary KPI: ' . $e->getMessage()
            ], 500);
        }
    }

    // GET /api/periods/rekap/bulanan - Report bulanan
    public function getMonthlyReport(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2020|max:2030',
            'bulan' => 'nullable|integer|min:1|max:12'
        ]);

        $tahun = $request->tahun;
        $bulan = $request->bulan;

        $query = Period::where(function ($q) use ($tahun) {
            $q->whereYear('tanggal_mulai', $tahun)
                ->orWhereYear('tanggal_selesai', $tahun);
        });

        if ($bulan) {
            $query->where(function ($q) use ($bulan) {
                $q->whereMonth('tanggal_mulai', $bulan)
                    ->orWhereMonth('tanggal_selesai', $bulan);
            });
        }

        $periods = $query->withCount(['attendances', 'kpiEvaluations'])
            ->orderBy('tanggal_mulai')
            ->get();

        $rekapBulanan = $periods->map(function ($period) {
            return [
                'periode_id' => $period->id_periode,
                'nama_periode' => $period->nama,
                'tanggal_mulai' => $period->tanggal_mulai,
                'tanggal_selesai' => $period->tanggal_selesai,
                'status' => $period->status,
                'jumlah_absensi' => $period->attendances_count,
                'jumlah_evaluasi_kpi' => $period->kpi_evaluations_count,
                'attendance_uploaded' => $period->attendance_uploaded
            ];
        });

        return response()->json([
            'success' => true,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'data' => $rekapBulanan
        ], 200);
    }

    // GET /api/periods/rekap/tahunan - Report tahunan
    public function getYearlyReport(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2020|max:2030'
        ]);

        $tahun = $request->tahun;

        $periods = Period::whereYear('tanggal_mulai', $tahun)
            ->orWhereYear('tanggal_selesai', $tahun)
            ->withCount(['attendances', 'kpiEvaluations'])
            ->orderBy('tanggal_mulai')
            ->get();

        $rekapTahunan = [
            'tahun' => $tahun,
            'total_periode' => $periods->count(),
            'total_absensi' => $periods->sum('attendances_count'),
            'total_evaluasi_kpi' => $periods->sum('kpi_evaluations_count'),
            'periode_aktif' => $periods->where('status', 'active')->count(),
            'periode_terkunci' => $periods->where('status', 'locked')->count(),
            'periode_draft' => $periods->where('status', 'draft')->count(),
            'detail_periode' => $periods->map(function ($period) {
                return [
                    'periode_id' => $period->id_periode,
                    'nama_periode' => $period->nama,
                    'status' => $period->status,
                    'jumlah_absensi' => $period->attendances_count,
                    'jumlah_evaluasi_kpi' => $period->kpi_evaluations_count
                ];
            })
        ];

        return response()->json([
            'success' => true,
            'data' => $rekapTahunan
        ], 200);
    }

    // POST /api/periods/auto-create-from-attendance - Auto create period dari attendance
    public function autoCreateFromAttendance()
    {
        try {
            $uniquePeriods = Attendance::whereNotNull('period')
                ->where('period', '!=', '')
                ->whereNotIn('period', function ($query) {
                    $query->select('nama')->from('periods');
                })
                ->distinct()
                ->pluck('period');

            $dibuatCount = 0;

            foreach ($uniquePeriods as $namaPeriod) {
                $dates = $this->parseNamaPeriod($namaPeriod);

                if ($dates) {
                    Period::create([
                        'nama' => $namaPeriod,
                        'tanggal_mulai' => $dates['start_date'],
                        'tanggal_selesai' => $dates['end_date'],
                        'status' => 'draft',
                        'attendance_uploaded' => false
                    ]);
                    $dibuatCount++;
                }
            }

            // Link attendance ke period
            $this->linkAttendancesToPeriods();

            return response()->json([
                'success' => true,
                'message' => "Berhasil membuat $dibuatCount periode baru dari data absensi",
                'dibuat_count' => $dibuatCount
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat periode otomatis: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper untuk parse nama period
    private function parseNamaPeriod($namaPeriod)
    {
        try {
            // Format: "August-2023" atau "August 2023 - September 2023"
            if (strpos($namaPeriod, '-') !== false) {
                $parts = array_map('trim', explode('-', $namaPeriod));

                if (count($parts) === 2) {
                    $bulan = $parts[0];
                    $tahun = $parts[1];

                    if (is_numeric($tahun)) {
                        $startDate = Carbon::createFromFormat('F Y', "$bulan $tahun")->startOfMonth();
                        $endDate = $startDate->copy()->endOfMonth();

                        return [
                            'start_date' => $startDate,
                            'end_date' => $endDate
                        ];
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    // Helper untuk link attendances ke periods
    private function linkAttendancesToPeriods()
    {
        $attendances = Attendance::whereNotNull('period')
            ->where('period', '!=', '')
            ->whereNull('periode_id')
            ->get();

        foreach ($attendances as $attendance) {
            $period = Period::where('nama', $attendance->period)->first();
            if ($period) {
                $attendance->update(['periode_id' => $period->id_periode]);
            }
        }
    }

    // GET /api/periods/status/check - Cek dan update status period
    public function checkPeriodStatuses()
    {
        try {
            // Auto-lock periods yang editing period sudah lewat
            $expiredPeriods = Period::where('editing_end_date', '<', now())
                ->where('status', 'active')
                ->get();

            $dikunciCount = 0;
            foreach ($expiredPeriods as $period) {
                $period->update(['status' => 'locked']);
                $dikunciCount++;
            }

            // Auto-activate periods yang attendance sudah diupload tapi masih draft
            $periodsToActivate = Period::where('attendance_uploaded', true)
                ->where('status', 'draft')
                ->get();

            $diaktifkanCount = 0;
            foreach ($periodsToActivate as $period) {
                $period->update(['status' => 'active']);
                $diaktifkanCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Otomatis mengunci $dikunciCount periode, mengaktifkan $diaktifkanCount periode",
                'dikunci_count' => $dikunciCount,
                'diaktifkan_count' => $diaktifkanCount
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek status periode: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPerformanceAcrossPeriods()
    {
        try {
            // Query utama, diperbaiki untuk menggunakan 'periods.nama'
            $results = DB::table('kpis_has_employees')
                ->join('roles_has_employees', 'kpis_has_employees.employees_id_karyawan', '=', 'roles_has_employees.employee_id')
                ->join('roles', 'roles_has_employees.role_id', '=', 'roles.id_jabatan')
                ->join('divisions', 'roles.division_id', '=', 'divisions.id_divisi')
                ->join('periods', 'kpis_has_employees.periode_id', '=', 'periods.id_periode')
                ->select(
                    'divisions.nama_divisi',
                    'periods.id_periode',
                    'periods.nama', // <-- INI YANG DIPERBAIKI
                    DB::raw('AVG(kpis_has_employees.nilai_akhir) as average_score')
                )
                ->groupBy('divisions.nama_divisi', 'periods.id_periode', 'periods.nama') // <-- INI JUGA DIPERBAIKI
                ->orderBy('periods.id_periode')
                ->get();

            if ($results->isEmpty()) {
                return response()->json(['success' => true, 'data' => ['categories' => [], 'series' => []]]);
            }

            // Susun data untuk frontend
            $categories = $results->pluck('nama')->unique()->values()->all(); // <-- INI JUGA DIPERBAIKI
            $seriesData = [];
            $groupedByDivision = $results->groupBy('nama_divisi');

            foreach ($groupedByDivision as $divisionName => $scores) {
                $dataPoints = [];
                foreach ($categories as $periodName) {
                    $scoreForPeriod = $scores->firstWhere('nama', $periodName); // <-- INI JUGA DIPERBAIKI
                    $dataPoints[] = $scoreForPeriod ? round($scoreForPeriod->average_score, 2) : null;
                }

                $seriesData[] = [
                    'name' => $divisionName,
                    'data' => $dataPoints,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'categories' => $categories,
                    'series' => $seriesData,
                ]
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getPerformanceAcrossPeriods: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }
}
    // Tambahkan method ini ke model Period (app/Models/Period.php)
