<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiQuestion extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_question';
    protected $fillable = ['kpi_id', 'pertanyaan', 'poin'];

    public function kpi()
    {
        return $this->belongsTo(Kpi::class, 'kpi_id', 'id_kpi');
    }
}
