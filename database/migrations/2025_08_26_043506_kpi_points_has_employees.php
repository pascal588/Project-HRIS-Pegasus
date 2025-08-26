<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kpi_points_has_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('KPI_Points_id_poin');
            $table->unsignedBigInteger('employees_id_karyawan');
            $table->decimal('nilai', 3, 1);
            $table->timestamps();

            $table->foreign('KPI_Points_id_poin')->references('id_poin')->on('KPI_Points')->onDelete('cascade');
            $table->foreign('employees_id_karyawan')->references('id_karyawan')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_points_has_employees');
    }
};
