<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_points_has_employee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpis_has_employee_id')
                ->constrained('kpis_has_employees')
                ->cascadeOnDelete();
            $table->foreignId('kpi_point_id')
                ->constrained('kpi_points', 'id_point')
                ->cascadeOnDelete();
            $table->decimal('bobot', 8, 2)->nullable();
            $table->decimal('nilai_akhir', 8, 2)->nullable();
            $table->decimal('nilai_absensi', 5, 2)->default(0);
            $table->timestamps();

            // Constraints
            $table->unique(['kpis_has_employee_id', 'kpi_point_id'], 'uq_subaspek_employee');

            // Indexes
            $table->index('kpis_has_employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_points_has_employee');
    }
};
