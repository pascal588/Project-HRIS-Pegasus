<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KpiSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kpis')->insert([
            ['id_kpi' => 1, 'nama' => 'Disiplin', 'deskripsi' => 'Kedisiplinan karyawan', 'bobot' => 3.0],
            ['id_kpi' => 2, 'nama' => 'Produktivitas', 'deskripsi' => 'Output pekerjaan', 'bobot' => 4.0],
            ['id_kpi' => 3, 'nama' => 'Kerjasama', 'deskripsi' => 'Kolaborasi tim', 'bobot' => 3.0],
        ]);
    }
}

