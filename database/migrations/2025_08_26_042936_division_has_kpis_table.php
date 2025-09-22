<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('division_has_kpis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_divisi');
            $table->unsignedBigInteger('kpis_id_kpi');
            $table->timestamps();

            $table->foreign('id_divisi')
                ->references('id_divisi')
                ->on('divisions')
                ->onDelete('cascade');

            $table->foreign('kpis_id_kpi')
                ->references('id_kpi')
                ->on('kpis')
                ->onDelete('cascade');

            $table->unique(['id_divisi', 'kpis_id_kpi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('division_has_kpis');
    }
};
