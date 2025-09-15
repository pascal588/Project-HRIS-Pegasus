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
        Schema::create('kpi_question_has_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_question_id_question')
                ->constrained('kpi_questions', 'id_question')
                ->cascadeOnDelete();
            $table->foreignId('employees_id_karyawan')
                ->constrained('employees', 'id_karyawan')
                ->cascadeOnDelete();
            $table->tinyInteger('nilai')->nullable(); // 1–4
            $table->timestamps();

            // Constraint biar 1 karyawan hanya punya 1 jawaban per pertanyaan
            $table->unique(['kpi_question_id_question', 'employees_id_karyawan'], 'uq_kpi_question_employee');

            // Index untuk query cepat berdasarkan karyawan
            $table->index('employees_id_karyawan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_question_has_employees');
    }
};
