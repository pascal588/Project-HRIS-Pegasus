<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
     protected $fillable = [
        'usersfix_id_user', 'nama', 'no_telp', 'gender', 'foto', 'role'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'usersfix_id_user', 'id');
    }
}
