<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'id_jabatan';
    protected $fillable = ['nama_jabatan', 'divisions_id_divisi'];

    public function employees()
    {
        return $this->belongsToMany(
            Employee::class,
            'roles_has_employees',
            'roles_id_jabatan',
            'employees_id_karyawan'
        );
    }

}
