<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiQuestionHasEmployee extends Model
{
    use HasFactory;

    protected $table = 'kpi_question_has_employees';
    
    protected $fillable = [
        'periode_id',
        'kpi_question_id_question',
        'employees_id_karyawan',
        'nilai',
        'is_finalized',
        'finalized_at',
        
    ];

    protected $casts = [
        'nilai' => 'integer',
        'is_finalized' => 'boolean',
        'finalized_at' => 'datetime'
    ];

    // Relationships
    public function question()
    {
        return $this->belongsTo(KpiQuestion::class, 'kpi_question_id_question', 'id_question');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employees_id_karyawan', 'id_karyawan');
    }

    public function period()
    {
        return $this->belongsTo(Period::class, 'periode_id', 'id_periode');
    }
}