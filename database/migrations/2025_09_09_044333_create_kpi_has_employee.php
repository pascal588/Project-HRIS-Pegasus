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
            $table->foreignId('periode_id')
                ->constrained('periods', 'id_periode')
                ->cascadeOnDelete();
            $table->year('tahun');
            $table->tinyInteger('bulan'); // 1-12
            $table->decimal('nilai_akhir', 8, 2)->nullable();
            $table->boolean('is_finalized')->default(false);
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Constraints
            $table->unique(['kpis_id_kpi', 'employees_id_karyawan', 'periode_id'], 'uq_kpi_employee_period');

            // Indexes
            $table->index('employees_id_karyawan');
            $table->index('periode_id');
            $table->index(['tahun', 'bulan']);
            $table->index('is_finalized');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpis_has_employees');
    }
};
