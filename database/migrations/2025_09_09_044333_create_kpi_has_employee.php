<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpis_has_employees', function (Blueprint $table) {
            $table->id('id');
            $table->foreignId('kpis_id_kpi')->constrained('kpis', 'id_kpi')->cascadeOnDelete();
            $table->unsignedBigInteger('employees_id_karyawan'); // FK ke tabel employees
            $table->year('tahun');
            $table->tinyInteger('bulan'); // 1-12
            $table->decimal('nilai_akhir', 8, 2)->nullable(); // hasil akhir KPI
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpis_has_employees');
    }
};
