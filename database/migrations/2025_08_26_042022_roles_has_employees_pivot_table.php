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
            $table->unsignedBigInteger('roles_id_jabatan');
            $table->unsignedBigInteger('employees_id_karyawan');
            $table->timestamps();

            $table->foreign('roles_id_jabatan')->references('id_jabatan')->on('roles')->onDelete('cascade');
            $table->foreign('employees_id_karyawan')->references('id_karyawan')->on('employees')->onDelete('cascade');
            $table->unique(['roles_id_jabatan', 'employees_id_karyawan'], 'role_employee_unique');

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
