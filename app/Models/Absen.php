<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class Absen extends Model
// {
//     use HasFactory;

//     protected $table = 'absen';
//     protected $primaryKey = 'id_absen';
    
//     protected $fillable = [
//         'employees_id_karyawan',
//         'jam_masuk',
//         'jam_keluar',
//         'status',
//         'lama_kerja'
//     ];

//     public function employee()
//     {
//         return $this->belongsTo(Employee::class, 'employees_id_karyawan', 'id_karyawan');
//     }
// }