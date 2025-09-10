<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kpi;

class KpiSeeder extends Seeder
{
    public function run(): void
    {
        $kpis = [
            ['nama' => 'Kompetensi Umum', 'deskripsi' => 'KPI kompetensi umum karyawan', 'bobot' => 50, 'is_global' => true],
            ['nama' => 'Kompetensi Teknis', 'deskripsi' => 'KPI kompetensi teknis karyawan', 'bobot' => 50, 'is_global' => true],
        ];

        foreach ($kpis as $kpi) {
            Kpi::create($kpi);
        }
    }
}
