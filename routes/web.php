<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// routes/web.php

// HR
// Route::get('/hr/dashboard', function () {
//     return view('HR.dashboard'); // nanti bikin file di resources/views/hr/dashboard.blade.php
// })->name('hr.dashboard');

// Penilai
Route::get('/penilai/dashboard', function () {
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


// Karyawan
Route::get('/karyawan/dashboard', function () {
    return view('karyawan.dashboard');
})->name('karyawan.dashboard');
Route::get('/karyawan/absen', function () {
    return view('karyawan.absen');
})->name('karyawan.absen');
Route::get('/karyawan/kpi', function () {
    return view('karyawan.kpi');
})->name('karyawan.kpi');


// nyoba
Route::get('/profile/edit', function () {
    return view('profile.edit');
})->name('profile.edit');
