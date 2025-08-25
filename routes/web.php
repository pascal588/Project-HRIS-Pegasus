<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:hr'])->group(function () {
    Route::get('/dashboard-hr', function() {
        return view('hr.dashboard');
    })->name('hr.dashboard');
});

Route::middleware(['auth', 'role:penilai'])->group(function () {
    Route::get('/dashboard-penilai', function() {
        return view('penilai.dashboard');
    })->name('penilai.dashboard');
    Route::get('/penilai/list-karyawan', function () {
    return view('penilai.list-karyawan');
    })->name('penilai.list-karyawan');
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

Route::middleware(['auth', 'role:karyawan'])->group(function () {
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

// Route::get('/profile/edit', function () {
//     return view('profile.edit');
// })->name('profile.edit');


require __DIR__.'/auth.php';
