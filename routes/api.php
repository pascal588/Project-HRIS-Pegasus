<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// routes/api.php
use App\Http\Controllers\Api\DivisionController;

// Gunakan full namespace dengan method array
Route::apiResource('Division', DivisionController::class);

