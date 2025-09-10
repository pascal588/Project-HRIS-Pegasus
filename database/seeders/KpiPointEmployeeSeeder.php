<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KpiPointEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kpi_points_has_employees')->insert([
            ['KPI_Points_id_point' => 1, 'employees_id_karyawan' => 1, 'nilai' => 3.5],
            ['KPI_Points_id_point' => 2, 'employees_id_karyawan' => 2, 'nilai' => 2.5],
            ['KPI_Points_id_point' => 3, 'employees_id_karyawan' => 3, 'nilai' => 3.0],
        ]);
    }
}
