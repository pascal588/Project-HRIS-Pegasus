<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
            $table->tinyInteger('nilai')->nullable(); // 1-4
            $table->boolean('is_finalized')->default(false);
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            // Constraints
            $table->unique(['kpi_question_id_question', 'employees_id_karyawan'], 'uq_kpi_question_employee');

            // Indexes
            $table->index('employees_id_karyawan');
            $table->index('is_finalized');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_question_has_employees');
    }
};
