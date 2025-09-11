<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            ['id_jabatan' => 1, 'nama_jabatan' => 'HR', 'division_id' => 1],
            ['id_jabatan' => 2, 'nama_jabatan' => 'Kepala Divisi', 'division_id' => 1],
            ['id_jabatan' => 3, 'nama_jabatan' => 'Karyawan', 'division_id' => 3],
        ]);
    }
}
