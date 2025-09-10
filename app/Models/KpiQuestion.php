<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiQuestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kpi_questions';
    protected $primaryKey = 'id_question';
    protected $fillable = ['kpi_point_id', 'pertanyaan'];

    // Relasi ke sub-aspek
    public function point()
    {
        return $this->belongsTo(KpiPoint::class, 'kpi_point_id', 'id_point');
    }

    // Relasi ke jawaban/nilai karyawan
    public function employeeScores()
    {
        return $this->hasMany(KpiQuestionHasEmployee::class, 'kpi_question_id_question', 'id_question');
    }
}
