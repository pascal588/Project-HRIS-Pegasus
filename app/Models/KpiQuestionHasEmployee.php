<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiQuestionHasEmployee extends Model
{
  use HasFactory;

  // Nama tabel
  protected $table = 'kpi_question_has_employees';

  // Fillable / mass assignable
  protected $fillable = [
    'kpi_question_id_question',
    'employees_id_karyawan',
    'nilai',
  ];

  /**
   * Relasi ke pertanyaan KPI
   */
  public function question()
  {
    return $this->belongsTo(KpiQuestion::class, 'kpi_question_id_question', 'id_question');
  }

  /**
   * Relasi ke karyawan
   */
  public function employee()
  {
    return $this->belongsTo(Employee::class, 'employees_id_karyawan', 'id_karyawan');
  }
}
