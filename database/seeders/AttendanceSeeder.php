<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();
        try {
            // Ambil periode aktif
            $periode = DB::table('periods')->where('is_active', true)->first();
            
            if (!$periode) {
                $this->command->info('Tidak ada periode aktif. Silakan jalankan PeriodSeeder terlebih dahulu.');
                return;
            }

            $employees = DB::table('employees')->where('status', 'Aktif')->get();
            $startDate = Carbon::parse($periode->tanggal_mulai);
            $endDate = Carbon::parse($periode->tanggal_selesai);

            $attendanceData = [];

            foreach ($employees as $employee) {
                $currentDate = $startDate->copy();
                
                while ($currentDate->lte($endDate)) {
                    // Skip weekend (Sabtu dan Minggu)
                    if (!$currentDate->isWeekend()) {
                        // Untuk HR (employee_id = 1), beri absensi lebih sedikit
                        if ($employee->id_karyawan == 1) {
                            // HR hanya hadir 70% dari hari kerja
                            $shouldPresent = rand(1, 100) <= 70;
                        } else {
                            // Karyawan lain hadir 85% dari hari kerja
                            $shouldPresent = rand(1, 100) <= 85;
                        }

                        if ($shouldPresent) {
                            $status = 'Present at workday (PW)';
                            $clockIn = Carbon::createFromTime(8, rand(0, 30), 0); // Jam 8:00-8:30
                            $clockOut = Carbon::createFromTime(17, rand(0, 30), 0); // Jam 17:00-17:30
                            
                            // Hitung total attendance (9 jam kerja - 1 jam istirahat)
                            $totalAttendance = '08:00:00';
                            $late = $clockIn->gt(Carbon::createFromTime(8, 15, 0)) ? rand(5, 30) : 0;
                        } else {
                            // Random status untuk yang tidak hadir
                            $statusOptions = ['Sick (S)', 'Permission (I)', 'Absent (A)'];
                            $status = $statusOptions[array_rand($statusOptions)];
                            $clockIn = null;
                            $clockOut = null;
                            $totalAttendance = '00:00:00';
                            $late = 0;
                        }

                        $attendanceData[] = [
                            'employee_id' => $employee->id_karyawan,
                            'periode_id' => $periode->id_periode,
                            'period' => $periode->nama,
                            'date' => $currentDate->format('Y-m-d'),
                            'start_date' => $periode->tanggal_mulai,
                            'end_date' => $periode->tanggal_selesai,
                            'status' => $status,
                            'work_pattern_clock_in' => '08:00',
                            'work_pattern_clock_out' => '17:00',
                            'work_pattern_late_tolerance' => '15',
                            'daily_attendance_clock_in' => $clockIn ? $clockIn->format('H:i:s') : null,
                            'daily_attendance_break' => $clockIn ? '12:00:00' : null,
                            'daily_attendance_after_break' => $clockIn ? '13:00:00' : null,
                            'daily_attendance_clock_out' => $clockOut ? $clockOut->format('H:i:s') : null,
                            'daily_attendance_overtime_in' => null,
                            'daily_attendance_overtime_out' => null,
                            'late' => $late,
                            'early_leave' => 0,
                            'total_attendance' => $totalAttendance,
                            'total_break_duration' => $clockIn ? '01:00:00' : null,
                            'total_overtime' => null,
                            'timezone_clock_in' => 'Asia/Jakarta',
                            'timezone_clock_out' => 'Asia/Jakarta',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    } else {
                        // Weekend - Non-working day
                        $attendanceData[] = [
                            'employee_id' => $employee->id_karyawan,
                            'periode_id' => $periode->id_periode,
                            'period' => $periode->nama,
                            'date' => $currentDate->format('Y-m-d'),
                            'start_date' => $periode->tanggal_mulai,
                            'end_date' => $periode->tanggal_selesai,
                            'status' => 'Non-working day (NW)',
                            'work_pattern_clock_in' => null,
                            'work_pattern_clock_out' => null,
                            'work_pattern_late_tolerance' => null,
                            'daily_attendance_clock_in' => null,
                            'daily_attendance_break' => null,
                            'daily_attendance_after_break' => null,
                            'daily_attendance_clock_out' => null,
                            'daily_attendance_overtime_in' => null,
                            'daily_attendance_overtime_out' => null,
                            'late' => null,
                            'early_leave' => null,
                            'total_attendance' => null,
                            'total_break_duration' => null,
                            'total_overtime' => null,
                            'timezone_clock_in' => null,
                            'timezone_clock_out' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    $currentDate->addDay();
                }
            }

            // Insert data absensi
            foreach (array_chunk($attendanceData, 100) as $chunk) {
                DB::table('attendances')->insert($chunk);
            }

            DB::commit();
            $this->command->info('AttendanceSeeder berhasil dijalankan!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('AttendanceSeeder gagal: ' . $e->getMessage());
            throw $e;
        }
    }
}