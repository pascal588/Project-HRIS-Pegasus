<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles'; 
    protected $primaryKey = 'id_jabatan';
    protected $fillable = ['nama_jabatan', 'division_id'];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'roles_has_employees',
        'role_id',        // FK di pivot → roles
        'employee_id',    // FK di pivot → employees
        'id_jabatan',     // PK di roles
        'id_karyawan'     // PK di employees
        )->withPivot('created_at', 'updated_at');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id', 'id_divisi');
    }
    public function kpis()
    {
        return $this->hasMany(Kpi::class, 'role_id', 'id_jabatan');
    }
}
