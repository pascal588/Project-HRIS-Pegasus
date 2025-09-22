<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->id('id_periode');
            $table->string('nama'); // e.g., "Agustus-September 2023"
            $table->boolean('kpi_published')->default(false);
            $table->timestamp('kpi_published_at')->nullable();
            $table->date('tanggal_mulai'); // e.g., 2023-08-07
            $table->date('tanggal_selesai'); // e.g., 2023-09-07
            $table->date('evaluation_start_date')->nullable(); // e.g., 2023-09-09
            $table->date('evaluation_end_date')->nullable(); // e.g., 2023-09-20
            $table->date('editing_start_date')->nullable(); // e.g., 2023-09-21
            $table->date('editing_end_date')->nullable(); // e.g., 2023-10-07
            $table->enum('status', ['draft', 'active', 'locked'])->default('draft');
            $table->boolean('attendance_uploaded')->default(false);
            $table->timestamp('attendance_uploaded_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('status');
            $table->index('is_active');
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
