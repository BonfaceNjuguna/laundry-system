<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\CustomerController;

Route::get('/test', function () {
    return response()->json(['message' => 'API working']);
});

Route::apiResource('bookings', BookingController::class);
Route::apiResource('services', ServiceController::class);
Route::apiResource('customers', CustomerController::class);
