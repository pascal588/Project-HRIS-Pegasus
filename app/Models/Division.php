<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Division extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'divisions';      // nama tabel
    protected $primaryKey = 'id_divisi'; // primary key

    protected $fillable = [
        'nama_divisi',
        'id_divisi'
    ];

    public function roles()
    {
        return $this->hasMany(Role::class, 'division_id', 'id_divisi');
    }
}