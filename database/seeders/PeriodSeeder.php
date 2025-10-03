<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PeriodSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus dulu periode yang mungkin sudah ada untuk menghindari duplikasi
        DB::table('periods')->where('id_periode', 1)->delete();

        DB::table('periods')->insert([
            [
                'id_periode' => 1,
                'nama' => 'September-Oktober 2024',
                'kpi_published' => true,
                'kpi_published_at' => now(),
                'tanggal_mulai' => '2024-09-01',
                'tanggal_selesai' => '2024-10-31',
                'evaluation_start_date' => '2024-11-01',
                'evaluation_end_date' => '2024-11-10',
                'editing_start_date' => '2024-11-11',
                'editing_end_date' => '2024-11-20',
                'status' => 'active',
                'attendance_uploaded' => true,
                'attendance_uploaded_at' => now(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        $this->command->info('PeriodDataSeeder berhasil dijalankan!');
    }
}