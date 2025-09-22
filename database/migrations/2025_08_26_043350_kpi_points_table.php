<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_points', function (Blueprint $table) {
            $table->id('id_point');
            $table->foreignId('kpis_id_kpi')
                ->constrained('kpis', 'id_kpi')
                ->cascadeOnDelete();
            $table->string('nama', 255);
            $table->decimal('bobot', 5, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kpis_id_kpi');
            $table->index(['kpis_id_kpi', 'bobot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_points');
    }
};
