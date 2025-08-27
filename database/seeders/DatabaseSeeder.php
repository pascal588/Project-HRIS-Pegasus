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
        // Usersfix
        DB::table('users')->insert([
            [
                'email' => 'hr@gmail.com',
                'password' => Hash::make('12345678'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'Kepala@gmail.com',
                'password' => Hash::make('12345678'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'karyawan@gmail.com',
                'password' => Hash::make('12345678'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Divisions
        DB::table('divisions')->insert([
            ['id_divisi' => 1, 'nama_divisi' => 'IT', 'created_at' => now(), 'updated_at' => now()],
            ['id_divisi' => 2, 'nama_divisi' => 'Finance', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Employees
        DB::table('employees')->insert([
            [
                'usersfix_id_user' => 1,
                'nama' => 'Budi HR',
                'no_telp' => '0811111111',
                'gender' => 'Pria',
                'foto' => null,
                'role' => 'HR',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usersfix_id_user' => 2,
                'nama' => 'Sinta Kepala',
                'no_telp' => '0822222222',
                'gender' => 'Wanita',
                'foto' => null,
                'role' => 'Kepala-divisi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usersfix_id_user' => 3,
                'nama' => 'Andi Karyawan',
                'no_telp' => '0833333333',
                'gender' => 'Pria',
                'foto' => null,
                'role' => 'Karyawan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Roles
        DB::table('roles')->insert([
            [
                'nama_jabatan' => 'HR',
                'divisions_id_divisi' => 1, // IT
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_jabatan' => 'Kepala-divisi',
                'divisions_id_divisi' => 1, // IT
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_jabatan' => 'Karyawan',
                'divisions_id_divisi' => 2, // Finance
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Roles_has_employees (pivot)
        DB::table('roles_has_employees')->insert([
            [
                'roles_id_jabatan' => 1, // HR
                'employees_id_karyawan' => 1, // Budi HR
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'roles_id_jabatan' => 2, // Kepala Divisi
                'employees_id_karyawan' => 2, // Sinta Kepala
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'roles_id_jabatan' => 3, // Karyawan
                'employees_id_karyawan' => 3, // Andi Karyawan
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
