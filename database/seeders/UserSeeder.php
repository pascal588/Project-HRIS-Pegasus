<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'email' => 'HR@gmail.com',
            'password' => Hash::make('12345678'),
        ]);

        User::create([
            'email' => 'kepala@gmail.com',
            'password' => Hash::make('12345678'),
        ]);

        User::create([
            'email' => 'karyawan@gmail.com',
            'password' => Hash::make('12345678'),
        ]);
    }
}
