<?php

use Illuminate\Support\Facades\Route;
// routes/api.php
use App\Http\Controllers\Api\DivisionController;
use App\Http\Controllers\Api\EmployeeApiController;
use App\Http\Controllers\Api\RoleApiController;
use App\Http\Controllers\Api\Rolecontroller;

// api divisi
Route::apiResource('divisions', DivisionController::class);

//api karyawan
Route::apiResource('employees', EmployeeApiController::class);

//api jabatan
route::apiResource('role', RoleApiController::class );


