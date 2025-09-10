<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';
    protected $primaryKey = 'id';

    protected $fillable = [
        'employee_id',
        'date',
        'period',
        'status',
        'work_pattern',
        'clock_in',
        'clock_out',
        'late_tolerance',
        'daily_attendance_clock_in',
        'break',
        'after_break',
        'daily_attendance_clock_out',
        'overtime_in',
        'overtime_out',
        'late',
        'early_leave',
        'total_attendance',
        'break_duration',
        'overtime',
        'timezone_clock_in',
        'timezone_clock_out'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id_karyawan');
    }
}