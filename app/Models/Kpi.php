<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kpi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kpis';
    protected $primaryKey = 'id_kpi';
    protected $fillable = [
        'nama',
        'bobot',
        'is_global',
        'periode_id',
    ];

    public function period()
    {
        return $this->belongsTo(Period::class, 'periode_id', 'id_periode');
    }

    public function points()
    {
        return $this->hasMany(KpiPoint::class, 'kpis_id_kpi', 'id_kpi');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'kpis_has_employees', 'kpis_id_kpi', 'employees_id_karyawan')
            ->withPivot('nilai_akhir', 'tahun', 'bulan')
            ->withTimestamps();
    }

    public function divisions()
    {
        return $this->belongsToMany(Division::class, 'division_has_kpis', 'kpis_id_kpi', 'id_divisi');
    }
}
