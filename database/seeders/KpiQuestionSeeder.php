<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KpiQuestionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kpi_questions')->insert([
            ['id_question' => 1, 'kpi_id' => 1, 'pertanyaan' => 'Apakah karyawan hadir tepat waktu?', 'poin' => 4],
            ['id_question' => 2, 'kpi_id' => 2, 'pertanyaan' => 'Apakah karyawan mencapai target?', 'poin' => 3],
            ['id_question' => 3, 'kpi_id' => 3, 'pertanyaan' => 'Apakah karyawan aktif bekerja sama?', 'poin' => 4],
        ]);
    }
}
