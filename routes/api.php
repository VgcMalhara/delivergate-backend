<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\CustomerController;

// Public route (No token required to log in)
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes (Requires a valid Sanctum token)
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::apiResource('items', InventoryItemController::class);
    Route::apiResource('customers', CustomerController::class);

});
