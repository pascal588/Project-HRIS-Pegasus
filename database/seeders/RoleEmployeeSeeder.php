<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles_has_employees')->insert([
            ['role_id' => 1, 'employee_id' => 1], // Admin HR as Manager
            ['role_id' => 2, 'employee_id' => 2], // Karyawan 1 as IT Staff
            ['role_id' => 3, 'employee_id' => 3], // Karyawan 2 as Finance Staff
        ]);
    }
}

