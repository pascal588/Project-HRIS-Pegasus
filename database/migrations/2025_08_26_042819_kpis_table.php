<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpis', function (Blueprint $table) {
            $table->id('id_kpi');
            $table->foreignId('periode_id')
                ->nullable()
                ->constrained('periods', 'id_periode')
                ->cascadeOnDelete();
            $table->string('nama', 100);
            $table->decimal('bobot', 5, 2); // e.g., 10.00
            $table->boolean('is_global')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('is_global');
            $table->index('periode_id');
            $table->index(['periode_id', 'is_global']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpis');
    }
};
