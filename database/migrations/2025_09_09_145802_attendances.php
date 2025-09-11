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
            $table->string('work_pattern_clock_in')->nullable();
            $table->string('work_pattern_clock_out')->nullable();
            $table->string('work_pattern_late_tolerance')->nullable();
            $table->time('daily_attendance_clock_in')->nullable();
            $table->time('daily_attendance_break')->nullable();
            $table->time('daily_attendance_after_break')->nullable();
            $table->time('daily_attendance_clock_out')->nullable();
            $table->time('daily_attendance_overtime_in')->nullable();
            $table->time('daily_attendance_overtime_out')->nullable();
            $table->integer('late')->nullable();
            $table->integer('early_leave')->nullable();
            $table->time('total_attendance')->nullable();
            $table->time('total_break_duration')->nullable();
            $table->time('total_overtime')->nullable();
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