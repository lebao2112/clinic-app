<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
// use App\Http\Controllers\UserController; 

// Public route for authentication
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require valid Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth endpoints (No permission check needed)
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Business logic routes (Require valid token AND specific permissions)
    Route::middleware('permission')->group(function () {
        
    });
});