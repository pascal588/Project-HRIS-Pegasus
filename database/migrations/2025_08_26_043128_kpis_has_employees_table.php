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
        Schema::create('kpis_has_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kpis_id_kpi');
            $table->unsignedBigInteger('employees_id_karyawan');
            $table->tinyInteger('periode_bulan');
            $table->year('periode_tahun');
            $table->float('point_total');
            $table->timestamps();

            $table->foreign('kpis_id_kpi')->references('id_kpi')->on('kpis')->onDelete('cascade');
            $table->foreign('employees_id_karyawan')->references('id_karyawan')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpis_has_employees');
    }
};
