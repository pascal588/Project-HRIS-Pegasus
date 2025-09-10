<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiHasEmployee extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'kpis_has_employees';
  protected $fillable = ['kpis_id_kpi', 'employees_id_karyawan', 'tahun', 'bulan', 'nilai_akhir'];

  public function kpi()
  {
    return $this->belongsTo(Kpi::class, 'kpis_id_kpi', 'id_kpi');
  }

  public function employee()
  {
    return $this->belongsTo(Employee::class, 'employees_id_karyawan', 'id_karyawan');
  }
}
