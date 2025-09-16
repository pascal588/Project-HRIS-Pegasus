<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PenilaiController;
use App\Http\Controllers\HrController;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.update.photo');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'nama_jabatan:HR'])->group(function () {
    Route::get('/dashboard-hr', function() {
        return view('hr.dashboard');
    })->name('hr.dashboard');
    Route::get('/penilaian', function() {
        return view('hr.penilaian');
    })->name('hr.penilaian');
    Route::get('/absensi', function() {
        return view('hr.absensi');
    })->name('hr.absensi');
    Route::get('/hr/detail-absensi/{employee_id}', [HrController::class, 'detailAbsensi'])->name('hr.detail-absensi');
    Route::get('/kpi-karyawan', function() {
        return view('hr.kpi-karyawan');
    })->name('hr.kpi-karyawan');
    Route::get('/detail-kpi', function() {
        return view('hr.detail-kpi');
    })->name('hr.detail-kpi');
    Route::get('/karyawan', function() {
        return view('hr.karyawan');
    })->name('hr.karyawan');
    Route::get('/divisi', function() {
        return view('hr.divisi');
    })->name('hr.divisi');
    Route::get('/kpi', function() {
        return view('hr.kpi');
    })->name('hr.kpi');
});

Route::middleware(['auth', 'nama_jabatan:Kepala Divisi'])->group(function () {
    Route::get('/dashboard-penilai', function() {
        return view('penilai.dashboard');
    })->name('penilai.dashboard');
    Route::get('/penilai/list-karyawan', [PenilaiController::class, 'listKaryawan'])->name('penilai.list-karyawan');
    Route::get('/penilai/absensi', function () {
        return view('penilai.absensi');
    })->name('penilai.absensi');
    Route::get('/penilai/kpi-karyawan', function () {
        return view('penilai.kpi-karyawan');
    })->name('penilai.kpi-karyawan');
    Route::get('/penilai/kpi-penilai', function () {
        return view('penilai.kpi-penilai');
    })->name('penilai.kpi-penilai');
});

Route::middleware(['auth', 'nama_jabatan:Karyawan'])->group(function () {
    Route::get('/dashboard-karyawan', function() {
        return view('karyawan.dashboard');
    })->name('karyawan.dashboard');
    Route::get('/karyawan/absen', function () {
    return view('karyawan.absen');
    })->name('karyawan.absen');
    Route::get('/karyawan/kpi', function () {
        return view('karyawan.kpi');
    })->name('karyawan.kpi');
});


require __DIR__.'/auth.php';