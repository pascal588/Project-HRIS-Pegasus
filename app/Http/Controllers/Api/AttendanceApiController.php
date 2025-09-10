<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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
            $query->whereHas('employee.roles', function($q) use ($request) {
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
// Di dalam method import(), perbaiki bagian pemrosesan data
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

        // Get employee ID and period from Excel - perbaikan untuk format khusus
        $employeeId = null;
        $period = null;
        
        // Cari metadata dengan lebih robust untuk format Excel ini
        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $index => $cell) {
                if (trim($cell) === 'Personnel ID' && isset($row[$index + 1])) {
                    $employeeId = trim($row[$index + 1]);
                }
                if (trim($cell) === 'Period' && isset($row[$index + 1])) {
                    $period = trim($row[$index + 1]);
                }
            }
            
            // Hentikan pencarian setelah menemukan kedua data
            if ($employeeId && $period) {
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

        // Find header row and map column indexes - sesuaikan dengan format Excel
        $headerRowIndex = null;
        $columnMapping = [];
        
        foreach ($rows as $index => $row) {
            // Look for header row dengan format yang sesuai dengan Excel
            $noIndex = array_search('No', $row);
            $dateIndex = array_search('Date', $row);
            $statusIndex = array_search('Status', $row);
            
            if ($noIndex !== false && $dateIndex !== false && $statusIndex !== false) {
                $headerRowIndex = $index;
                
                // Map column names to their indexes - sesuaikan dengan struktur Excel
                $columnMapping = [
                    'no' => $noIndex,
                    'date' => $dateIndex,
                    'status' => $statusIndex,
                    'work_pattern' => array_search('Work Pattern', $row) !== false ? array_search('Work Pattern', $row) : null,
                    'clock_in' => array_search('Clock-in', $row) !== false ? array_search('Clock-in', $row) : null,
                    'clock_out' => array_search('Clock-out', $row) !== false ? array_search('Clock-out', $row) : null,
                    'late_tolerance' => array_search('Late Tolerance', $row) !== false ? array_search('Late Tolerance', $row) : null,
                    'daily_attendance_clock_in' => $this->findColumnIndex($row, ['Daily Attendance', 'Clock-in']),
                    'break' => array_search('Break', $row) !== false ? array_search('Break', $row) : null,
                    'after_break' => array_search('After Break', $row) !== false ? array_search('After Break', $row) : null,
                    'daily_attendance_clock_out' => $this->findColumnIndex($row, ['Daily Attendance', 'Clock-out']),
                    'overtime_in' => array_search('Overtime In', $row) !== false ? array_search('Overtime In', $row) : null,
                    'overtime_out' => array_search('Overtime Out', $row) !== false ? array_search('Overtime Out', $row) : null,
                    'late' => array_search('Late', $row) !== false ? array_search('Late', $row) : null,
                    'early_leave' => array_search('Early Leave', $row) !== false ? array_search('Early Leave', $row) : null,
                    'total_attendance' => $this->findColumnIndex($row, ['Total', 'Attendance']),
                    'break_duration' => $this->findColumnIndex($row, ['Break', 'Break Duration']),
                    'overtime' => array_search('Overtime', $row) !== false ? array_search('Overtime', $row) : null,
                    'timezone_clock_in' => $this->findColumnIndex($row, ['Timezone', 'Clock-in']),
                    'timezone_clock_out' => $this->findColumnIndex($row, ['Timezone', 'Clock-out']),
                ];
                
                break;
            }
        }

        if ($headerRowIndex === null) {
            return response()->json([
                'success' => false,
                'message' => 'Could not find header row in Excel file'
            ], 400);
        }

        $importedCount = 0;
        $skippedCount = 0;

        // Process attendance data starting from row after header
        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            // Skip empty rows or rows without date
            if (empty($row[$columnMapping['no']]) || !is_numeric($row[$columnMapping['no']])) {
                continue;
            }

            try {
                // Parse date safely - handle format "6 Aug 2025"
                $dateValue = $row[$columnMapping['date']];
                $date = \Carbon\Carbon::createFromFormat('d M Y', $dateValue)->format('Y-m-d');
                
                // Konversi status dari format Excel ke format database jika perlu
                $status = isset($columnMapping['status']) && isset($row[$columnMapping['status']]) ? $row[$columnMapping['status']] : null;
                
                // Pastikan status sesuai dengan enum yang ditentukan
                $validStatuses = [
                    'Present at workday (PW)', 
                    'Non-working day (NW)', 
                    'Absent (A)', 
                    'Sick (S)', 
                    'Permission (I)'
                ];
                
                if (!in_array($status, $validStatuses)) {
                    // Coba mapping jika status tidak persis sama
                    $statusMap = [
                        'Present at workday (PW)' => 'Present at workday (PW)',
                        'Non-working day (NW)' => 'Non-working day (NW)',
                        'Absent (A)' => 'Absent (A)',
                        'Sick (S)' => 'Sick (S)',
                        'Permission (I)' => 'Permission (I)'
                    ];
                    
                    $status = $statusMap[$status] ?? 'Absent (A)'; // Default to Absent if not recognized
                }
                
                // Map Excel columns to database fields using column mapping
                $attendanceData = [
                    'employee_id' => $employeeId,
                    'period' => $period,
                    'date' => $date,
                    'status' => $status,
                    'work_pattern' => isset($columnMapping['work_pattern']) && isset($row[$columnMapping['work_pattern']]) ? $row[$columnMapping['work_pattern']] : null,
                    'clock_in' => isset($columnMapping['clock_in']) && isset($row[$columnMapping['clock_in']]) ? $this->parseTime($row[$columnMapping['clock_in']]) : null,
                    'clock_out' => isset($columnMapping['clock_out']) && isset($row[$columnMapping['clock_out']]) ? $this->parseTime($row[$columnMapping['clock_out']]) : null,
                    'late_tolerance' => isset($columnMapping['late_tolerance']) && isset($row[$columnMapping['late_tolerance']]) ? $this->parseInt($row[$columnMapping['late_tolerance']]) : null,
                    'daily_attendance_clock_in' => isset($columnMapping['daily_attendance_clock_in']) && isset($row[$columnMapping['daily_attendance_clock_in']]) ? $this->parseTime($row[$columnMapping['daily_attendance_clock_in']]) : null,
                    'break' => isset($columnMapping['break']) && isset($row[$columnMapping['break']]) ? $this->parseTime($row[$columnMapping['break']]) : null,
                    'after_break' => isset($columnMapping['after_break']) && isset($row[$columnMapping['after_break']]) ? $this->parseTime($row[$columnMapping['after_break']]) : null,
                    'daily_attendance_clock_out' => isset($columnMapping['daily_attendance_clock_out']) && isset($row[$columnMapping['daily_attendance_clock_out']]) ? $this->parseTime($row[$columnMapping['daily_attendance_clock_out']]) : null,
                    'overtime_in' => isset($columnMapping['overtime_in']) && isset($row[$columnMapping['overtime_in']]) ? $this->parseTime($row[$columnMapping['overtime_in']]) : null,
                    'overtime_out' => isset($columnMapping['overtime_out']) && isset($row[$columnMapping['overtime_out']]) ? $this->parseTime($row[$columnMapping['overtime_out']]) : null,
                    'late' => isset($columnMapping['late']) && isset($row[$columnMapping['late']]) ? $this->parseInt($row[$columnMapping['late']]) : null,
                    'early_leave' => isset($columnMapping['early_leave']) && isset($row[$columnMapping['early_leave']]) ? $this->parseInt($row[$columnMapping['early_leave']]) : null,
                    'total_attendance' => isset($columnMapping['total_attendance']) && isset($row[$columnMapping['total_attendance']]) ? $this->parseTimeDuration($row[$columnMapping['total_attendance']]) : null,
                    'break_duration' => isset($columnMapping['break_duration']) && isset($row[$columnMapping['break_duration']]) ? $this->parseTimeDuration($row[$columnMapping['break_duration']]) : null,
                    'overtime' => isset($columnMapping['overtime']) && isset($row[$columnMapping['overtime']]) ? $this->parseTimeDuration($row[$columnMapping['overtime']]) : null,
                    'timezone_clock_in' => isset($columnMapping['timezone_clock_in']) && isset($row[$columnMapping['timezone_clock_in']]) ? $row[$columnMapping['timezone_clock_in']] : null,
                    'timezone_clock_out' => isset($columnMapping['timezone_clock_out']) && isset($row[$columnMapping['timezone_clock_out']]) ? $row[$columnMapping['timezone_clock_out']] : null,
                ];

                // Check if attendance already exists for this date and employee
                $existing = Attendance::where('employee_id', $employeeId)
                    ->where('date', $attendanceData['date'])
                    ->first();

                if ($existing) {
                    // Update existing record
                    $existing->update($attendanceData);
                    $importedCount++;
                } else {
                    // Create new record
                    Attendance::create($attendanceData);
                    $importedCount++;
                }
            } catch (\Exception $e) {
                Log::error('Error processing row ' . $i . ': ' . $e->getMessage());
                $skippedCount++;
                continue;
            }
        }

        // Refresh periods cache after import
        $this->refreshPeriodsCache();

        return response()->json([
            'success' => true,
            'message' => 'Imported ' . $importedCount . ' attendance records successfully',
            'skipped' => $skippedCount,
            'period' => $period
        ], 200);

    } catch (\Exception $e) {
        Log::error('Attendance import error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to import attendance data: ' . $e->getMessage()
        ], 500);
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
            $query->where('period', $request->period); // FILTER BERDASARKAN PERIODE YANG DISIMPAN
        }
        
        if ($request->has('division_id') && !empty($request->division_id)) {
            $query->whereHas('employee.roles', function($q) use ($request) {
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
            
            $summary[] = [
                'employee_id' => $employeeId,
                'nama' => $employee->nama,
                'division' => $employee->roles->first() ? $employee->roles->first()->division->nama_divisi : '-',
                'hadir' => $records->where('status', 'Present at workday (PW)')->count(),
                'izin' => $records->where('status', 'Permission (I)')->count(),
                'sakit' => $records->where('status', 'Sick (S)')->count(),
                'mangkir' => $records->where('status', 'Absent (A)')->count(),
                'terlambat' => $records->sum('late'),
                'actions' => '<a href="'.route('hr.detail-absensi', ['employee_id' => $employeeId]).'" class="btn btn-outline-secondary btn-sm"><i class="icofont-eye-alt"></i></a>'
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
            ->with('roles.division')
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