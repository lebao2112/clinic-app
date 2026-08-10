<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SpecialtyController; 
use App\Http\Controllers\DoctorController;

// Public route for authentication
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require valid Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth endpoints
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // User Management Routes (Require valid token AND USERS.* permission)
    Route::middleware('permission:USERS.*')->group(function () {
        
        // Status update route (MUST be placed before apiResource)
        Route::patch('users/{user}/status', [UserController::class, 'updateStatus']);
        
        // Full CRUD routes (includes index, store, show, update, destroy)
        Route::apiResource('users', UserController::class);
    });

    // Specialty Management Routes (Require valid token AND SPECIALTIES.* permission)
    Route::middleware('permission:SPECIALTIES.*')->group(function () {
        Route::apiResource('specialties', SpecialtyController::class);
    });

    // Doctor Management Routes (Require valid token AND DOCTORS.* permission)
    Route::middleware('permission:DOCTORS.*')->group(function () {
        Route::apiResource('doctors', DoctorController::class);
    });
   
});