<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absen', function (Blueprint $table) {
            $table->id('id_absen');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Mangkir']);
            $table->unsignedBigInteger('employees_id_karyawan');
            $table->integer('lama_kerja')->nullable();
            $table->timestamps();

            $table->foreign('employees_id_karyawan')->references('id_karyawan')->on('employees')->onDelete('cascade');
            // Tambahkan unique constraint jika perlu
            $table->unique(['employees_id_karyawan', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absen');
    }
};