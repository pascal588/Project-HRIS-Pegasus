<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_questions', function (Blueprint $table) {
            $table->id('id_question');
            $table->foreignId('kpi_point_id')
                ->constrained('kpi_points', 'id_point')
                ->cascadeOnDelete();
            $table->string('pertanyaan'); // teks pertanyaan
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_questions');
    }
};
