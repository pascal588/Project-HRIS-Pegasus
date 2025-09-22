<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DivisionHasKpi extends Model
{
    use HasFactory;

    protected $table = 'division_has_kpis';
    protected $fillable = [
        'id_divisi',
        'kpis_id_kpi',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class, 'id_divisi', 'id_divisi');
    }

    public function kpi()
    {
        return $this->belongsTo(Kpi::class, 'kpis_id_kpi', 'id_kpi');
    }
}
