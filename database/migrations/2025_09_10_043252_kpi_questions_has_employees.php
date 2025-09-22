<?php
// File: 2025_09_10_043252_kpi_questions_has_employees.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_question_has_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')
                ->nullable()
                ->constrained('periods', 'id_periode')
                ->nullOnDelete();
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

            // ✅ PERBAIKAN: Tambahkan periode_id ke unique constraint
            $table->unique(['kpi_question_id_question', 'employees_id_karyawan', 'periode_id'], 'uq_kpi_question_employee_period');

            // Indexes dengan nama manual yang lebih pendek
            $table->index('employees_id_karyawan', 'idx_employee_id');
            $table->index('is_finalized', 'idx_is_finalized');
            $table->index('periode_id', 'idx_periode_id');
            $table->index(['employees_id_karyawan', 'periode_id'], 'idx_employee_periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_question_has_employees');
    }
};