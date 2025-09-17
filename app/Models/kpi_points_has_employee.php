<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiPointHasEmployee extends Model
{
    use HasFactory;

    protected $table = 'kpi_points_has_employee';

    protected $fillable = [
        'kpis_has_employee_id',
        'kpi_point_id',
        'nilai',
    ];

    public function kpiHasEmployee()
    {
        return $this->belongsTo(KpiHasEmployee::class, 'kpis_has_employee_id');
    }

    public function point()
    {
        return $this->belongsTo(KpiPoint::class, 'kpi_point_id');
    }
}
