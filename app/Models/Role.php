<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'id_jabatan';
    protected $fillable = ['nama_jabatan', 'division_id'];

    public function employees() {
        return $this->belongsToMany(Employee::class, 'roles_has_employees', 'role_id', 'employee_id');
    }

    public function division() {
        return $this->belongsTo(Division::class, 'division_id', 'id_divisi');
    }


}
