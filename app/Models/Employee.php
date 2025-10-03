<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employees';
    protected $primaryKey = 'id_karyawan';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id_karyawan',
        'user_id',
        'nama',
        'no_telp',
        'gender',
        'foto',
        'status'
    ];

    //relasi ke users
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    //relasi ke roles
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'roles_has_employees', 'employee_id', 'role_id', 'id_karyawan', 'id_jabatan')
            ->withPivot('created_at', 'updated_at');
    }

    //relasi ke divisi
    public function division()
    {
        return $this->belongsTo(Division::class, 'divisions_id_divisi', 'id_divisi');
    }


    public function kpiPoints()
    {
        return $this->belongsToMany(KpiPoint::class, 'kpi_points_has_employees', 'employees_id_karyawan', 'KPI_Points_id_poin')
            ->withPivot('nilai')
            ->withTimestamps();
    }

    public function kpiFinalScores()
    {
        return $this->hasMany(KpiHasEmployee::class, 'employees_id_karyawan', 'id_karyawan');
    }

    public function divisionThroughRole()
{
    return $this->hasOneThrough(
        Division::class,
        Role::class,
        'id_jabatan',      // FK di roles
        'id_divisi',       // FK di divisions  
        'id_karyawan',     // PK di employees
        'division_id'      // FK di roles ke divisions
    );
}

// TAMBAHKAN ACCESSOR UNTUK MEMUDAHKAN
public function getDivisiAttribute()
{
    // Coba akses melalui role pertama
    if ($this->roles->isNotEmpty()) {
        return $this->roles->first()->division;
    }
    return null;
}

public function getNamaDivisiAttribute()
{
    return $this->divisi ? $this->divisi->nama_divisi : 'Tidak Ada Divisi';
}

public function getJabatanAttribute()
{
    return $this->roles->first()->nama_jabatan ?? 'Tidak Ada Jabatan';
}
}
