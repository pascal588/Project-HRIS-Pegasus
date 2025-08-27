<?php

use Illuminate\Support\Facades\Route;
// routes/api.php
use App\Http\Controllers\Api\DivisionController;


// Gunakan full namespace dengan method array
// Route::apiResource('/Division', DivisionController::class);
// Route::get('/Division', [DivisionController::class, 'index']);

// Atau gunakan string syntax
Route::apiResource('divisions', 'App\Http\Controllers\Api\DivisionController');

