<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        Employee::create([
            'user_id' => 1,
            'nama' => 'Admin HR',
            'no_telp' => '0811111111',
            'gender' => 'Pria',
        ]);

        Employee::create([
            'user_id' => 2,
            'nama' => 'Karyawan 1',
            'no_telp' => '0822222222',
            'gender' => 'Wanita',
        ]);

        Employee::create([
            'user_id' => 3,
            'nama' => 'Karyawan 2',
            'no_telp' => '0833333333',
            'gender' => 'Pria',
        ]);
    }
}
