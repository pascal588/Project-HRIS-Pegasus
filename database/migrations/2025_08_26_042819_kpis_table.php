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
            $table->string('nama', 100); // contoh: Disiplin, Teknis
            $table->text('deskripsi')->nullable();
            $table->decimal('bobot', 5, 2); // persentase bobot KPI
            $table->boolean('is_global')->default(true); // true = global, false = khusus divisi
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpis');
    }
};
