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

    protected $fillable = [
        'user_id',
        'nama',
        'no_telp',
        'gender',
        'foto',
        'role'
    ];

        //relasi ke users
    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    //relasi ke roles
    public function roles() {
        return $this->belongsToMany(Role::class, 'roles_has_employees', 'employee_id', 'role_id');
    }

}
