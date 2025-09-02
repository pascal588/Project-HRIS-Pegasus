<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiPoint extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id_poin';
    protected $fillable = ['nama'];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'kpi_points_has_employees', 'KPI_Points_id_poin', 'employees_id_karyawan')
                    ->withPivot('nilai')
                    ->withTimestamps();
    }
}
