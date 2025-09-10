<?php

namespace App\Http\Controllers;

use App\Models\Absen;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('absensi');
    }
    
    public function getData(Request $request)
    {
        $attendances = Absen::with('employee')
            ->selectRaw('absen.*, employees.nama as nama_karyawan')
            ->join('employees', 'absen.employees_id_karyawan', '=', 'employees.id_karyawan')
            ->orderBy('absen.created_at', 'desc');
            
        return DataTables::of($attendances)
            ->addColumn('tanggal', function($row) {
                return Carbon::parse($row->created_at)->format('d M Y');
            })
            ->addColumn('lama_kerja', function($row) {
                if ($row->lama_kerja) {
                    $hours = floor($row->lama_kerja / 60);
                    $minutes = $row->lama_kerja % 60;
                    return $hours . ' jam ' . $minutes . ' menit';
                }
                return '-';
            })
            ->addColumn('action', function($row) {
                return '<button class="btn btn-sm btn-outline-secondary view-detail" data-id="' . $row->id_absen . '">
                    <i class="icofont-eye text-primary"></i>
                </button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    
    public function getSummary(Request $request)
    {
        $currentYear = date('Y');
        $currentMonth = date('m');
        
        // Hitung ringkasan bulan ini
        $summary = Absen::selectRaw('
            SUM(CASE WHEN status = "Hadir" THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN status = "Izin" THEN 1 ELSE 0 END) as izin,
            SUM(CASE WHEN status = "Sakit" THEN 1 ELSE 0 END) as sakit,
            SUM(CASE WHEN status = "Mangkir" THEN 1 ELSE 0 END) as mangkir,
            COUNT(*) as total
        ')
        ->whereYear('created_at', $currentYear)
        ->whereMonth('created_at', $currentMonth)
        ->first();
        
        // Data untuk chart (6 bulan terakhir)
        $chartData = [];
        $categories = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->format('m');
            $year = $date->format('Y');
            $monthName = $date->format('M');
            
            $monthData = Absen::selectRaw('
                SUM(CASE WHEN status = "Hadir" THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = "Izin" THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status = "Sakit" THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN status = "Mangkir" THEN 1 ELSE 0 END) as mangkir
            ')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->first();
            
            $categories[] = $monthName;
            $chartData['hadir'][] = $monthData->hadir ?? 0;
            $chartData['izin'][] = $monthData->izin ?? 0;
            $chartData['sakit'][] = $monthData->sakit ?? 0;
            $chartData['mangkir'][] = $monthData->mangkir ?? 0;
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'hadir' => $summary->hadir,
                'izin' => $summary->izin,
                'sakit' => $summary->sakit,
                'mangkir' => $summary->mangkir,
                'terlambat' => 0, // Anda bisa menambahkan logika untuk menghitung keterlambatan
                'total_hari' => $summary->total,
                'chart_data' => [
                    'hadir' => $chartData['hadir'],
                    'izin' => $chartData['izin'],
                    'sakit' => $chartData['sakit'],
                    'mangkir' => $chartData['mangkir'],
                    'bulan' => $categories
                ]
            ]
        ]);
    }
}