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
        Schema::create('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('id_karyawan')->primary();
            $table->unsignedBigInteger('user_id'); // FK ke tabel users
            $table->string('nama', 100);
            $table->string('no_telp', 15);
            $table->enum('gender', ['Pria', 'Wanita']);
            $table->string('foto', 255)->nullable();
            $table->enum('status', ['Aktif', 'Non-Aktif', 'Cuti'])->default('Aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
