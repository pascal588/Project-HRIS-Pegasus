<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionKpiSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('divisions_has_kpis')->insert([
            ['division_id' => 1, 'kpis_id_kpi' => 1],
            ['division_id' => 2, 'kpis_id_kpi' => 2],
            ['division_id' => 3, 'kpis_id_kpi' => 3],
        ]);
    }
}
