<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('period')->nullable();
            $table->date('date');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['Present at workday (PW)', 'Non-working day (NW)', 'Absent (A)', 'Sick (S)', 'Permission (I)']);
            $table->string('work_pattern')->nullable();
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->integer('late_tolerance')->nullable();
            $table->time('daily_attendance_clock_in')->nullable();
            $table->time('break')->nullable();
            $table->time('after_break')->nullable();
            $table->time('daily_attendance_clock_out')->nullable();
            $table->time('overtime_in')->nullable();
            $table->time('overtime_out')->nullable();
            $table->integer('late')->nullable();
            $table->integer('early_leave')->nullable();
            $table->time('total_attendance')->nullable();
            $table->time('break_duration')->nullable();
            $table->time('overtime')->nullable();
            $table->string('timezone_clock_in')->nullable();
            $table->string('timezone_clock_out')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id_karyawan')->on('employees')->onDelete('cascade');
            $table->unique(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};