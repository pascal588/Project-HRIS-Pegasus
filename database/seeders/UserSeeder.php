<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         User::create([
            'name' => 'HR User',
            'email' => 'hr@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'hr',
        ]);

        User::create([
            'name' => 'Penilai User',
            'email' => 'penilai@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'penilai',
        ]);

        User::create([
            'name' => 'Karyawan User',
            'email' => 'karyawan@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'karyawan',
        ]);
    }
}
