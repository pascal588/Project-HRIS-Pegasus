<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kpi;
use App\Models\KpiPoint;

class KpiPointSeeder extends Seeder
{
  public function run(): void
  {
    // Ambil semua KPI
    $kpis = Kpi::all();

    foreach ($kpis as $kpi) {
      // Contoh sub-aspek
      $subAspeks = [
        ['nama' => 'Kerja Tim', 'bobot' => 5],
        ['nama' => 'Kedisiplinan', 'bobot' => 5],
        ['nama' => 'Komunikasi', 'bobot' => 5],
        ['nama' => 'Inisiatif', 'bobot' => 5],
        ['nama' => 'Problem Solving', 'bobot' => 5],
      ];

      foreach ($subAspeks as $sub) {
        KpiPoint::create([
          'kpis_id_kpi' => $kpi->id_kpi,
          'nama' => $sub['nama'],
          'bobot' => $sub['bobot'],
        ]);
      }
    }
  }
}
