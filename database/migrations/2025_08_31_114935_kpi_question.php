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
        Schema::create('kpi_questions', function (Blueprint $table) {
            $table->id('id_question');
            $table->unsignedBigInteger('kpi_id'); // FK ke KPI (aspek)
            $table->string('pertanyaan', 255);
            $table->enum('poin', [1, 2, 3, 4]); // poin hanya 1–4
            $table->timestamps();

            $table->foreign('kpi_id')->references('id_kpi')->on('kpis')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_questions');
    }
};
