<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KpiPointSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kpi_points')->insert([
            ['id_poin' => 1, 'nama' => 'Sangat Baik'],
            ['id_poin' => 2, 'nama' => 'Baik'],
            ['id_poin' => 3, 'nama' => 'Cukup'],
            ['id_poin' => 4, 'nama' => 'Kurang'],
        ]);
    }
}
