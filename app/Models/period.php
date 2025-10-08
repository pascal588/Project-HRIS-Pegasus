<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Period extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'periods';
    protected $primaryKey = 'id_periode';
    protected $fillable = [
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'kpi_published',
        'kpi_published_at',
        'evaluation_start_date',
        'evaluation_end_date',
        'editing_start_date',
        'editing_end_date',
        'status',
        'attendance_uploaded',
        'attendance_uploaded_at',
        'is_active',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',  
        'attendance_uploaded_at' => 'datetime',
    ];

    // 🔗 RELATIONS
    public function kpis()
    {
        return $this->hasMany(Kpi::class, 'periode_id', 'id_periode');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'periode_id', 'id_periode');
    }

    public function kpiEvaluations()
    {
        return $this->hasMany(KpiHasEmployee::class, 'periode_id', 'id_periode');
    }

    // 🎯 BUSINESS LOGIC METHODS
    public function isDateInPeriod($date)
    {
        $date = Carbon::parse($date);
        return $date->between(
            Carbon::parse($this->tanggal_mulai),
            Carbon::parse($this->tanggal_selesai)
        );
    }

    public function isEvaluationPeriod()
    {
        if (!$this->evaluation_start_date || !$this->evaluation_end_date) {
            return false;
        }

        $today = now()->format('Y-m-d');
        return $today >= $this->evaluation_start_date->format('Y-m-d') &&
            $today <= $this->evaluation_end_date->format('Y-m-d');
    }

    public function isEditingPeriod()
    {
        if (!$this->editing_start_date || !$this->editing_end_date) {
            return false;
        }

        $today = now()->format('Y-m-d');
        return $today >= $this->editing_start_date->format('Y-m-d') &&
            $today <= $this->editing_end_date->format('Y-m-d');
    }

    public function canBeEvaluated()
    {
        return $this->status === 'active' &&
            $this->attendance_uploaded &&
            $this->isEvaluationPeriod();
    }

    public function canBeEdited()
    {
        return $this->status === 'active' &&
            $this->isEditingPeriod();
    }

    public function isLocked()
    {
        return $this->status === 'locked';
    }

    // 🔍 SCOPES
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeLocked($query)
    {
        return $query->where('status', 'locked');
    }

    public function scopeWithAttendanceUploaded($query)
    {
        return $query->where('attendance_uploaded', true);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('tanggal_mulai', '<=', $date)
            ->where('tanggal_selesai', '>=', $date);
    }
}