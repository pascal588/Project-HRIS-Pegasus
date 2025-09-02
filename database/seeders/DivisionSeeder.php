<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('divisions')->insert([
            ['id_divisi' => 1, 'nama_divisi' => 'HR'],
            ['id_divisi' => 2, 'nama_divisi' => 'IT'],
            ['id_divisi' => 3, 'nama_divisi' => 'Finance'],
        ]);
    }
}
