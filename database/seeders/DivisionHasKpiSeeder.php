<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionHasKpiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('division_has_kpis')->insert([
            'id_divisi' => 1,
            'kpis_id_kpi' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
