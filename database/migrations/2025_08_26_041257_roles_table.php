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
        Schema::create('roles', function (Blueprint $table) {
            $table->id('id_jabatan');
            $table->string('nama_jabatan', 50);
            $table->unsignedBigInteger('divisions_id_divisi'); // FK ke divisions
            $table->timestamps();

            $table->foreign('divisions_id_divisi')->references('id_divisi')->on('divisions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
