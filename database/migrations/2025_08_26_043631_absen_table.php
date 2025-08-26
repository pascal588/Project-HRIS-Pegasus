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
        Schema::create('absen', function (Blueprint $table) {
            $table->id('id_absen');
            $table->time('jam_masuk');
            $table->time('jam_keluar');
            $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Mangkir']);
            $table->unsignedBigInteger('employees_id_karyawan');
            $table->timestamps();

            $table->foreign('employees_id_karyawan')->references('id_karyawan')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absen');
    }
};
