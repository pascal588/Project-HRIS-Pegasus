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
        Schema::create('roles_has_employees', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('role_id'); // FK ke roles
        $table->unsignedBigInteger('employee_id'); // FK ke employees
        $table->timestamps();

        $table->foreign('role_id')->references('id_jabatan')->on('roles')->onDelete('cascade');
        $table->foreign('employee_id')->references('id_karyawan')->on('employees')->onDelete('cascade');
        $table->unique(['role_id', 'employee_id']);
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles_has_employees');
    }
};
