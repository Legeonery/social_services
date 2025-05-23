<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;

Route::get('/services-api', [ServiceController::class, 'index']);
Route::post('/services-api', [ServiceController::class, 'store']);
Route::put('/services-api/{service}', [ServiceController::class, 'update']);
Route::delete('/services-api/{service}', [ServiceController::class, 'destroy']);