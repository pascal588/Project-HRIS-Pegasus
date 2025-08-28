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
        Schema::create('divisions_has_kpis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('division_id');
            $table->unsignedBigInteger('kpis_id_kpi');
            $table->timestamps();

            $table->foreign('division_id')->references('id_divisi')->on('divisions')->onDelete('cascade');
            $table->foreign('kpis_id_kpi')->references('id_kpi')->on('kpis')->onDelete('cascade');
            $table->unique(['division_id', 'kpis_id_kpi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('divisons_has_kpis');
    }
};
