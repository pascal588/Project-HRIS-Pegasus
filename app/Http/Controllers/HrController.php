<?php

namespace App\Http\Controllers;

use App\Models\KpiQuestionHasEmployee;
use Illuminate\Http\Request;
use App\Models\Employee;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MonthlyKpiExport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Exception;

class HrController extends Controller
{
    // ... (method-method lain yang tidak berubah) ...
    public function detailAbsensi($employee_id)
    {
        return view('hr.detail-absensi', compact('employee_id'));
    }

    private function fetchAndProcessKpiData($employeeId)
    {
        $kpiAnswers = KpiQuestionHasEmployee::with(['question.point.kpi', 'period'])
            ->where('employees_id_karyawan', $employeeId)
            ->whereNotNull('periode_id')
            ->get();

        if ($kpiAnswers->isEmpty()) {
            return collect();
        }

        $groupedByPeriod = $kpiAnswers->groupBy('periode_id');

        $monthlyData = $groupedByPeriod->map(function ($answersInPeriod) {
            $period = $answersInPeriod->first()->period;
            if (!$period) return null;

            $groupedByPoint = $answersInPeriod->groupBy('question.kpi_point_id');
            $totalScore = 0;

            $details = $groupedByPoint->map(function ($answersForPoint) use (&$totalScore) {
                $point = $answersForPoint->first()->question->point;
                if (!$point || !$point->kpi) return null;

                $averageAnswer = $answersForPoint->avg('nilai');
                $weight = $point->bobot;

                $subAspectScore = ($averageAnswer * 2.5) * ($weight / 100);
                $totalScore += $subAspectScore;

                return [
                    'aspect_name'       => $point->kpi->nama,
                    'sub_aspect_name'   => $point->nama,
                    'sub_aspect_weight' => $weight,
                    'average_answer'    => round($averageAnswer, 2),
                    'sub_aspect_score'  => round($subAspectScore, 2),
                ];
            })->filter();

            return [
                'period_id'   => $period->id,
                'month'       => $period->month,
                'year'        => $period->year,
                'month_name'  => date('F', mktime(0, 0, 0, $period->month, 10)),
                'total_score' => round($totalScore, 2),
                'details'     => $details->values(),
            ];
        })->filter();

        return $monthlyData->values();
    }

    /**
     * Method untuk mengubah data KPI menjadi format pivot (DIMODIFIKASI).
     *
     * @param \Illuminate\Support\Collection $kpiData
     * @return array
     */
    private function pivotKpiData(Collection $kpiData): array
    {
        $pivotedScores = [];
        $pointsWithWeights = [];
        $monthlyTotals = [];
        $allMonths = [];

        foreach ($kpiData as $periodData) {
            $monthName = $periodData['month_name'];
            $allMonths[] = $monthName;
            $monthlyTotals[$monthName] = $monthlyTotals[$monthName] ?? 0; // Inisialisasi total bulan

            foreach ($periodData['details'] as $detail) {
                $pointName = $detail['sub_aspect_name'];

                // Simpan bobot untuk setiap poin KPI
                $pointsWithWeights[$pointName] = $detail['sub_aspect_weight'];

                // Buat struktur data [Nama Poin][Nama Bulan] = Skor
                $pivotedScores[$pointName][$monthName] = $detail['sub_aspect_score'];

                // Tambahkan skor ke total bulanan
                $monthlyTotals[$monthName] += $detail['sub_aspect_score'];
            }
        }

        // Urutkan bulan sesuai kalender
        $monthOrder = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $uniqueMonths = array_unique($allMonths);
        usort($uniqueMonths, function ($a, $b) use ($monthOrder) {
            return array_search($a, $monthOrder) - array_search($b, $monthOrder);
        });

        // Urutkan poin KPI berdasarkan nama
        ksort($pointsWithWeights);

        return [
            'points' => $pointsWithWeights, // Sekarang berisi nama dan bobot
            'months' => $uniqueMonths,
            'scores' => $pivotedScores,
            'totals' => $monthlyTotals,   // Data total skor per bulan
        ];
    }

    public function getMonthlyKpiData($employeeId)
    {
        Log::info("Memulai pengambilan data KPI bulanan untuk employee_id: {$employeeId}");
        try {
            $employee = Employee::with('division')->find($employeeId);
            if (!$employee) {
                Log::warning("Employee tidak ditemukan dengan ID: {$employeeId}");
                return response()->json(['message' => 'Employee not found'], 404);
            }
            $processedKpiData = $this->fetchAndProcessKpiData($employeeId);
            $responseData = [
                'employee' => [
                    'id' => $employee->id_karyawan,
                    'name' => $employee->nama,
                    'division' => $employee->division->nama_divisi ?? 'N/A',
                ],
                'kpi_data' => $processedKpiData
            ];
            Log::info("Berhasil mengambil data KPI untuk employee_id: {$employeeId}");
            return response()->json($responseData);
        } catch (Exception $e) {
            Log::error("Error saat getMonthlyKpiData untuk employee_id: {$employeeId}", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Terjadi kesalahan pada server.'], 500);
        }
    }

    public function exportMonthlyKpi($employeeId)
    {
        Log::info("Memulai proses ekspor KPI bulanan untuk employee_id: {$employeeId}");
        try {
            $employee = Employee::with('division')->find($employeeId);
            if (!$employee) {
                return redirect()->back()->with('error', 'Employee not found.');
            }

            $kpiData = $this->fetchAndProcessKpiData($employeeId);

            if ($kpiData->isEmpty()) {
                return redirect()->back()->with('error', 'No KPI data to export for this employee.');
            }

            // Panggil method pivot yang sudah dimodifikasi
            $pivotedKpiData = $this->pivotKpiData($kpiData);

            // 1. Ambil data periode terakhir dari koleksi KPI
            $lastPeriod = $kpiData->last();

            // 2. Format bulan dan tahun (misal: 09-2025)
            $dateString = sprintf('%02d-%d', $lastPeriod['month'], $lastPeriod['year']);

            // 3. Ganti spasi di nama karyawan dengan underscore
            $employeeName = str_replace(' ', '_', $employee->nama);

            // 4. Buat nama file sesuai format yang Anda inginkan
            $fileName = "recap_karyawan_{$employeeName}_{$dateString}.xlsx";


            return Excel::download(new MonthlyKpiExport($employee, $pivotedKpiData), $fileName);
        } catch (Exception $e) {
            Log::error("Error saat exportMonthlyKpi untuk employee_id: {$employeeId}", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Gagal mengekspor data. Silakan coba lagi.');
        }
    }
}
