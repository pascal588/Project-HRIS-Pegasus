<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kpi extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_kpi';
    protected $fillable = ['nama', 'deskripsi', 'bobot', 'role_id'];

    public function divisions()
    {
        return $this->belongsToMany(Division::class, 'divisions_has_kpis', 'kpis_id_kpi', 'division_id')
                    ->withTimestamps();
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id_jabatan');
    }

    public function questions()
    {
        return $this->hasMany(KpiQuestion::class, 'kpi_id', 'id_kpi');
    }
}
