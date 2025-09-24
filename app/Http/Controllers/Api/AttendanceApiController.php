<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Period;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AttendanceApiController extends Controller
{
    // GET /api/attendances
    public function index(Request $request)
    {
        $query = Attendance::with('employee');

        // Filter by period
        if ($request->has('period')) {
            $period = explode(' - ', $request->period);
            if (count($period) === 2) {
                $startDate = \Carbon\Carbon::createFromFormat('d M Y', $period[0])->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromFormat('d M Y', $period[1])->format('Y-m-d');
                $query->whereBetween('date', [$startDate, $endDate]);
            }
        }

        // Filter by employee
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by division
        if ($request->has('division_id')) {
            $query->whereHas('employee.roles', function ($q) use ($request) {
                $q->where('division_id', $request->division_id);
            });
        }

        $attendances = $query->get();

        return response()->json([
            'success' => true,
            'data' => $attendances
        ], 200);
    }

    // POST /api/attendances/import
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Get employee ID and period from Excel
            $employeeId = null;
            $periodString = null;

            foreach ($rows as $rowIndex => $row) {
                foreach ($row as $index => $cell) {
                    if (trim($cell) === 'Personnel ID' && isset($row[$index + 1])) {
                        $employeeId = trim($row[$index + 1]);
                    }
                    if (trim($cell) === 'Period' && isset($row[$index + 1])) {
                        $periodString = trim($row[$index + 1]);
                    }
                }

                if ($employeeId && $periodString) {
                    break;
                }
            }

            if (!$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee ID not found in the Excel file'
                ], 400);
            }

            // Check if employee exists
            $employee = Employee::where('id_karyawan', $employeeId)->first();
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee with ID ' . $employeeId . ' not found in database'
                ], 400);
            }

            // ✅ AUTO-CREATE/LINK PERIOD (YANG DITAMBAH)
            $period = Period::firstOrCreate(
                ['nama' => $periodString],
                $this->createPeriodData($periodString) // ✅ METHOD BARU
            );

            // Find header rows - kita perlu membaca dua baris header
            $headerRowIndex1 = null;
            $headerRowIndex2 = null;
            $columnMapping = [];

            foreach ($rows as $index => $row) {
                // Cari baris dengan "No", "Date", "Status"
                if (in_array('No', $row) && in_array('Date', $row) && in_array('Status', $row)) {
                    $headerRowIndex1 = $index;
                    $headerRowIndex2 = $index + 1; // Baris header kedua ada di bawahnya
                    break;
                }
            }

            if ($headerRowIndex1 === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not find header row in Excel file'
                ], 400);
            }

            // Dapatkan kedua baris header
            $headerRow1 = $rows[$headerRowIndex1];
            $headerRow2 = $rows[$headerRowIndex2];

            // Buat mapping kolom berdasarkan kedua baris header
            $columnMapping = $this->createColumnMapping($headerRow1, $headerRow2);

            $importedCount = 0;
            $skippedCount = 0;

            // Process attendance data starting from row after second header
            for ($i = $headerRowIndex2 + 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                // Skip empty rows atau rows tanpa nomor
                if (empty($row[$columnMapping['no']]) || !is_numeric($row[$columnMapping['no']])) {
                    continue;
                }

                try {
                    // Parse date
                    $dateValue = $row[$columnMapping['date']];
                    $date = \Carbon\Carbon::createFromFormat('d M Y', $dateValue)->format('Y-m-d');

                    // ✅ VALIDASI TANGGAL DALAM PERIODE (YANG DITAMBAH)
                    if (!$period->isDateInPeriod($date)) {
                        Log::warning("Date $date not in period {$period->nama}");
                        $skippedCount++;
                        continue;
                    }

                    // Process status
                    $status = $row[$columnMapping['status']] ?? null;
                    $statusMap = [
                        'Present at workday (PW)' => 'Present at workday (PW)',
                        'Non-working day (NW)' => 'Non-working day (NW)',
                        'Absent (A)' => 'Absent (A)',
                        'Sick (S)' => 'Sick (S)',
                        'Permission (I)' => 'Permission (I)'
                    ];

                    $status = $statusMap[$status] ?? 'Absent (A)';

                    // Prepare attendance data
                    $attendanceData = [
                        'employee_id' => $employeeId,
                        'periode_id' => $period->id_periode, // ✅ DITAMBAH
                        'period' => $periodString,
                        'date' => $date,
                        'status' => $status,
                        'work_pattern_clock_in' => $this->getCellValue($row, $columnMapping, 'work_pattern_clock_in'),
                        'work_pattern_clock_out' => $this->getCellValue($row, $columnMapping, 'work_pattern_clock_out'),
                        'work_pattern_late_tolerance' => $this->getCellValue($row, $columnMapping, 'work_pattern_late_tolerance', 'int'),
                        'daily_attendance_clock_in' => $this->getCellValue($row, $columnMapping, 'daily_attendance_clock_in'),
                        'daily_attendance_break' => $this->getCellValue($row, $columnMapping, 'daily_attendance_break'),
                        'daily_attendance_after_break' => $this->getCellValue($row, $columnMapping, 'daily_attendance_after_break'),
                        'daily_attendance_clock_out' => $this->getCellValue($row, $columnMapping, 'daily_attendance_clock_out'),
                        'daily_attendance_overtime_in' => $this->getCellValue($row, $columnMapping, 'daily_attendance_overtime_in'),
                        'daily_attendance_overtime_out' => $this->getCellValue($row, $columnMapping, 'daily_attendance_overtime_out'),
                        'late' => $this->getCellValue($row, $columnMapping, 'late', 'int'),
                        'early_leave' => $this->getCellValue($row, $columnMapping, 'early_leave', 'int'),
                        'total_attendance' => $this->getCellValue($row, $columnMapping, 'total_attendance', 'duration'),
                        'total_break_duration' => $this->getCellValue($row, $columnMapping, 'total_break_duration', 'duration'),
                        'total_overtime' => $this->getCellValue($row, $columnMapping, 'total_overtime', 'duration'),
                        'timezone_clock_in' => $this->getCellValue($row, $columnMapping, 'timezone_clock_in'),
                        'timezone_clock_out' => $this->getCellValue($row, $columnMapping, 'timezone_clock_out'),
                    ];

                    // Check if attendance already exists
                    $existing = Attendance::where('employee_id', $employeeId)
                        ->where('date', $attendanceData['date'])
                        ->first();

                    if ($existing) {
                        $existing->update($attendanceData);
                        $importedCount++;
                    } else {
                        Attendance::create($attendanceData);
                        $importedCount++;
                    }
                } catch (\Exception $e) {
                    Log::error('Error processing row ' . $i . ': ' . $e->getMessage());
                    $skippedCount++;
                    continue;
                }
            }

            // ✅ UPDATE STATUS PERIODE SETELAH IMPORT (YANG DITAMBAH)
            // PERBAIKI CARA MEMANGGIL CONTROLLER:
            if ($importedCount > 0) {
                $period->update([
                    'attendance_uploaded' => true,
                    'attendance_uploaded_at' => now(),
                    'status' => 'active',
                    'is_active' => true
                ]);

                // ✅ COPY TEMPLATES KE PERIODE INI (FIXED)
                $kpiController = new \App\Http\Controllers\Api\KpiController();
                $result = $kpiController->copyTemplatesToPeriod($period->id_periode);

                // Handle response properly
                if ($result->getStatusCode() !== 200) {
                    Log::error('Failed to copy KPI templates', [
                        'response' => $result->getData()
                    ]);
                } else {
                    Log::info('KPI templates copied successfully', [
                        'copied_count' => $result->getData()->copied_count
                    ]);
                }
            }

            // Refresh periods cache after import
            $this->refreshPeriodsCache();

            return response()->json([
                'success' => true,
                'message' => 'Imported ' . $importedCount . ' attendance records successfully',
                'skipped' => $skippedCount,
                'period' => $periodString,
                'period_id' => $period->id_periode // ✅ DITAMBAH

            ], 200);
        } catch (\Exception $e) {
            Log::error('Attendance import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to import attendance data: ' . $e->getMessage()
            ], 500);
        }

        $this->autoLinkAttendancesToPeriods();
    }

    private function autoLinkAttendancesToPeriods()
{
    $attendances = Attendance::whereNull('periode_id')
        ->whereNotNull('period')
        ->get();
        
    foreach ($attendances as $attendance) {
        $period = Period::where('nama', $attendance->period)->first();
        if ($period) {
            $attendance->update(['periode_id' => $period->id_periode]);
            
            // Tandai period sebagai memiliki attendance
            if (!$period->attendance_uploaded) {
                $period->update([
                    'attendance_uploaded' => true,
                    'attendance_uploaded_at' => now()
                ]);
            }
        }
    }
}

    // Tambahkan method ini di AttendanceApiController
    private function createPeriodData($periodString)
    {
        // Parse period string untuk dapat tanggal mulai dan selesai
        $dates = $this->parsePeriodDates($periodString);

        return [
            'nama' => $periodString,
            'tanggal_mulai' => $dates['start_date'] ?? now()->startOfMonth(),
            'tanggal_selesai' => $dates['end_date'] ?? now()->endOfMonth(),
            'status' => 'draft',
            'attendance_uploaded' => false,
            'is_active' => false,
            'evaluation_start_date' => $dates['end_date'] ?
                Carbon::parse($dates['end_date'])->addDays(2) : null,
            'evaluation_end_date' => $dates['end_date'] ?
                Carbon::parse($dates['end_date'])->addDays(12) : null,
            'editing_start_date' => $dates['end_date'] ?
                Carbon::parse($dates['end_date'])->addDays(13) : null,
            'editing_end_date' => $dates['end_date'] ?
                Carbon::parse($dates['end_date'])->addDays(30) : null,
        ];
    }

    private function parsePeriodDates($periodString)
    {
        try {
            // Format: "7 Sep 2025 - 7 Okt 2025"
            if (strpos($periodString, '-') !== false) {
                $parts = array_map('trim', explode('-', $periodString));

                if (count($parts) === 2) {
                    return [
                        'start_date' => Carbon::createFromFormat('d M Y', trim($parts[0])),
                        'end_date' => Carbon::createFromFormat('d M Y', trim($parts[1]))
                    ];
                }
            }

            // Fallback: jika parsing gagal, gunakan bulan ini
            return [
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth()
            ];
        } catch (\Exception $e) {
            Log::warning("Failed to parse period dates: " . $e->getMessage());
            return [
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth()
            ];
        }
    }

    // Helper method untuk membuat mapping kolom dari dua baris header
    private function createColumnMapping($headerRow1, $headerRow2)
    {
        $mapping = [
            'no' => array_search('No', $headerRow1),
            'date' => array_search('Date', $headerRow1),
            'status' => array_search('Status', $headerRow1),
        ];

        // Mapping untuk Work Pattern
        $workPatternIndex = array_search('Work Pattern', $headerRow1);
        if ($workPatternIndex !== false) {
            $mapping['work_pattern_clock_in'] = $this->findSubColumnIndex($headerRow2, 'Clock-in', $workPatternIndex);
            $mapping['work_pattern_clock_out'] = $this->findSubColumnIndex($headerRow2, 'Clock-out', $workPatternIndex);
            $mapping['work_pattern_late_tolerance'] = $this->findSubColumnIndex($headerRow2, 'Late Tolerance', $workPatternIndex);
        }

        // Mapping untuk Daily Attendance
        $dailyAttendanceIndex = array_search('Daily Attendance', $headerRow1);
        if ($dailyAttendanceIndex !== false) {
            $mapping['daily_attendance_clock_in'] = $this->findSubColumnIndex($headerRow2, 'Clock-in', $dailyAttendanceIndex);
            $mapping['daily_attendance_break'] = $this->findSubColumnIndex($headerRow2, 'Break', $dailyAttendanceIndex);
            $mapping['daily_attendance_after_break'] = $this->findSubColumnIndex($headerRow2, 'After Break', $dailyAttendanceIndex);
            $mapping['daily_attendance_clock_out'] = $this->findSubColumnIndex($headerRow2, 'Clock-out', $dailyAttendanceIndex);
            $mapping['daily_attendance_overtime_in'] = $this->findSubColumnIndex($headerRow2, 'Overtime In', $dailyAttendanceIndex);
            $mapping['daily_attendance_overtime_out'] = $this->findSubColumnIndex($headerRow2, 'Overtime Out', $dailyAttendanceIndex);
        }

        // Mapping untuk kolom lainnya
        $mapping['late'] = array_search('Late', $headerRow1);
        $mapping['early_leave'] = array_search('Early Leave', $headerRow1);

        // Mapping untuk Total
        $totalIndex = array_search('Total', $headerRow1);
        if ($totalIndex !== false) {
            $mapping['total_attendance'] = $this->findSubColumnIndex($headerRow2, 'Attendance', $totalIndex);
            $mapping['total_break_duration'] = $this->findSubColumnIndex($headerRow2, 'Break', $totalIndex);
            $mapping['total_overtime'] = $this->findSubColumnIndex($headerRow2, 'Overtime', $totalIndex);
        }

        // Mapping untuk Timezone
        $timezoneIndex = array_search('Timezone', $headerRow1);
        if ($timezoneIndex !== false) {
            $mapping['timezone_clock_in'] = $this->findSubColumnIndex($headerRow2, 'Clock-in', $timezoneIndex);
            $mapping['timezone_clock_out'] = $this->findSubColumnIndex($headerRow2, 'Clock-out', $timezoneIndex);
        }

        return $mapping;
    }

    // Helper untuk mencari sub-kolom di bawah header utama
    private function findSubColumnIndex($headerRow2, $columnName, $startIndex)
    {
        // Cari kolom dengan nama yang sesuai, mulai dari index tertentu
        for ($i = $startIndex; $i < count($headerRow2); $i++) {
            if (trim($headerRow2[$i]) === $columnName) {
                return $i;
            }
        }
        return null;
    }

    // Helper untuk mendapatkan nilai cell dengan tipe data yang sesuai
    private function getCellValue($row, $columnMapping, $key, $type = 'string')
    {
        if (!isset($columnMapping[$key]) || !isset($row[$columnMapping[$key]])) {
            return null;
        }

        $value = $row[$columnMapping[$key]];

        if ($value === '-' || $value === '' || $value === null) {
            return null;
        }

        switch ($type) {
            case 'int':
                return $this->parseInt($value);
            case 'duration':
                return $this->parseTimeDuration($value);
            case 'time':
                return $this->parseTime($value);
            default:
                return $value;
        }
    }

    // Helper method to find column index with multiple possible names
    private function findColumnIndex($row, $possibleNames)
    {
        foreach ($possibleNames as $name) {
            $index = array_search($name, $row);
            if ($index !== false) {
                return $index;
            }
        }
        return null;
    }

    // Refresh periods cache after import
    private function refreshPeriodsCache()
    {
        // Clear cache
        Cache::forget('attendance_periods');
    }

    // Helper methods for parsing data
    private function parseTime($value)
    {
        if (empty($value) || $value === '-') {
            return null;
        }

        try {
            return \Carbon\Carbon::createFromFormat('H:i', $value)->format('H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseInt($value)
    {
        if (empty($value) || $value === '-') {
            return null;
        }

        return (int) $value;
    }

    private function parseTimeDuration($value)
    {
        if (empty($value) || $value === '-') {
            return null;
        }

        // Handle time duration like "8:18"
        if (strpos($value, ':') !== false) {
            $parts = explode(':', $value);
            $hours = (int) $parts[0];
            $minutes = (int) $parts[1];

            return sprintf('%02d:%02d:00', $hours, $minutes);
        }

        return null;
    }

    // GET /api/attendances/summary
    public function getSummary(Request $request)
    {
        $query = Attendance::with('employee.roles.division');

        // Apply filters
        if ($request->has('period') && !empty($request->period)) {
            $query->where('period', $request->period);
        }

        if ($request->has('division_id') && !empty($request->division_id)) {
            $query->whereHas('employee.roles', function ($q) use ($request) {
                $q->where('division_id', $request->division_id);
            });
        }

        if ($request->has('year') && !empty($request->year)) {
            $query->whereYear('date', $request->year);
        }

        $attendances = $query->get();

        // Group by employee and calculate summary
        $summary = [];
        foreach ($attendances->groupBy('employee_id') as $employeeId => $records) {
            $employee = $records->first()->employee;

            // Hitung jumlah keterlambatan
            $terlambatCount = $records->filter(function ($record) {
                return !empty($record->late) && $record->late > 0;
            })->count();

            $summary[] = [
                'employee_id' => $employeeId,
                'nama' => $employee->nama,
                'division' => $employee->roles->first() ? $employee->roles->first()->division->nama_divisi : '-',
                'hadir' => $records->where('status', 'Present at workday (PW)')->count(),
                'izin' => $records->where('status', 'Permission (I)')->count(),
                'sakit' => $records->where('status', 'Sick (S)')->count(),
                'mangkir' => $records->where('status', 'Absent (A)')->count(),
                'terlambat' => $records->sum('late'),
                'jumlah_terlambat' => $terlambatCount,
                'actions' => '<a href="' . route('hr.detail-absensi', ['employee_id' => $employeeId]) . '" class="btn btn-outline-secondary btn-sm"><i class="icofont-eye-alt"></i></a>'
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $summary
        ], 200);
    }

    // GET /api/attendances/periods
    public function getPeriods()
    {
        $refresh = request()->has('refresh') && request()->refresh == 'true';

        if ($refresh) {
            Cache::forget('attendance_periods');
        }

        $periods = Cache::remember('attendance_periods', 3600, function () {
            // Ambil semua periode unik dari database
            $uniquePeriods = Attendance::select('period')
                ->whereNotNull('period')
                ->where('period', '!=', '')
                ->distinct()
                ->orderByRaw("STR_TO_DATE(SUBSTRING_INDEX(period, ' - ', 1), '%e %b %Y') DESC")
                ->pluck('period')
                ->toArray();

            return $uniquePeriods;
        });

        return response()->json([
            'success' => true,
            'data' => $periods
        ], 200);
    }

    // GET /api/attendances/{id}
    public function show($id)
    {
        try {
            $attendance = Attendance::with('employee')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $attendance
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found'
            ], 404);
        }
    }

    // GET /api/attendances/employee/{employee_id}
    public function getEmployeeAttendance($employee_id, Request $request)
    {
        try {
            // Cari employee berdasarkan id_karyawan
            $employee = Employee::where('id_karyawan', $employee_id)
                ->with(['roles.division', 'roles' => function ($query) {
                    $query->select('id_jabatan', 'nama_jabatan', 'division_id');
                }])
                ->firstOrFail();

            $query = Attendance::where('employee_id', $employee_id);

            // Filter by period if provided
            if ($request->has('period') && !empty($request->period)) {
                $query->where('period', $request->period);
            }

            $attendances = $query->orderBy('date', 'desc')->get();

            // Calculate summary
            $summary = [
                'hadir' => $attendances->where('status', 'Present at workday (PW)')->count(),
                'izin' => $attendances->where('status', 'Permission (I)')->count(),
                'sakit' => $attendances->where('status', 'Sick (S)')->count(),
                'mangkir' => $attendances->where('status', 'Absent (A)')->count(),
                'terlambat' => $attendances->sum('late'),
                'jumlah_terlambat' => $attendances->filter(function ($record) {
                    return !empty($record->late) && $record->late > 0;
                })->count(),
            ];

            // Get unique periods for this employee
            $periods = Attendance::where('employee_id', $employee_id)
                ->whereNotNull('period')
                ->where('period', '!=', '')
                ->distinct()
                ->pluck('period');

            return response()->json([
                'success' => true,
                'employee' => $employee,
                'summary' => $summary,
                'attendances' => $attendances,
                'periods' => $periods
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found: ' . $e->getMessage()
            ], 404);
        }
    }
}