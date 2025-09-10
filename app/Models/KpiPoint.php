<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiPoint extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kpi_points';
    protected $primaryKey = 'id_point';
    protected $fillable = ['kpis_id_kpi', 'nama', 'bobot'];

    // Relasi ke KPI (aspek utama)
    public function kpi()
    {
        return $this->belongsTo(Kpi::class, 'kpis_id_kpi', 'id_kpi');
    }

    // Relasi ke pertanyaan
    public function questions()
    {
        return $this->hasMany(KpiQuestion::class, 'kpi_point_id', 'id_point');
    }

    // Relasi untuk ambil nilai karyawan langsung dari pertanyaan
    public function employeeScores()
    {
        return $this->hasManyThrough(
            KpiQuestionHasEmployee::class, // target akhir
            KpiQuestion::class,            // lewat model KpiQuestion
            'kpi_point_id',                // FK di kpi_questions
            'kpi_question_id_question',    // FK di kpi_question_has_employees
            'id_point',                    // PK di kpi_points
            'id_question'                  // PK di kpi_questions
        );
    }
}
