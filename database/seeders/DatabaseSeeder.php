<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            DivisionSeeder::class,
            RoleSeeder::class,
            EmployeeSeeder::class,
            RoleEmployeeSeeder::class,
            KpiSeeder::class,
            DivisionKpiSeeder::class,
            KpiPointSeeder::class,
            KpiPointEmployeeSeeder::class,
            KpiQuestionSeeder::class,
        ]);
    }
}
