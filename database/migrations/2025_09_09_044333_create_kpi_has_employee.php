<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpis_has_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpis_id_kpi')
                ->constrained('kpis', 'id_kpi')
                ->cascadeOnDelete();
            $table->foreignId('employees_id_karyawan')
                ->constrained('employees', 'id_karyawan')
                ->cascadeOnDelete();
            $table->year('tahun');
            $table->tinyInteger('bulan'); // 1–12
            $table->decimal('nilai_akhir', 8, 2)->nullable(); // hasil akhir KPI
            $table->timestamps();
            $table->softDeletes();

            // Tidak boleh ada duplikat nilai KPI untuk periode yang sama
            $table->unique(['kpis_id_kpi', 'employees_id_karyawan', 'tahun', 'bulan'], 'uq_kpi_employee_period');

            // Optimisasi query by employee
            $table->index('employees_id_karyawan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpis_has_employees');
    }
};
