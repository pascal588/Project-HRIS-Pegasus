<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KpiPointEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kpi_points_has_employees')->insert([
<<<<<<< HEAD
            ['KPI_Points_id_point' => 1, 'employees_id_karyawan' => 1, 'nilai' => 3.5],
            ['KPI_Points_id_point' => 2, 'employees_id_karyawan' => 2, 'nilai' => 2.5],
            ['KPI_Points_id_point' => 3, 'employees_id_karyawan' => 3, 'nilai' => 3.0],
=======
            ['KPI_Points_id_poin' => 1, 'employees_id_karyawan' => 1, 'nilai' => 3.5],
            ['KPI_Points_id_poin' => 2, 'employees_id_karyawan' => 2, 'nilai' => 2.5],
            ['KPI_Points_id_poin' => 3, 'employees_id_karyawan' => 2018213, 'nilai' => 3.0],
>>>>>>> ad960aa04afd681da03650f5380246d99319cc65
        ]);
    }
}
