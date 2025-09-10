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
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

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