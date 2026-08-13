<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SpecialtyController; 
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController; 
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ExaminationController;
use App\Http\Middleware\EnsurePermission;
use App\Http\Controllers\MedicineController;

// Public route for authentication
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', EnsurePermission::class])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('users/{user}/status', [UserController::class, 'updateStatus']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('specialties', SpecialtyController::class);
    Route::apiResource('doctors', DoctorController::class);
    Route::apiResource('patients', PatientController::class);
    Route::patch('appointments/{id}/status', [AppointmentController::class, 'changeStatus']);
    Route::apiResource('appointments', AppointmentController::class);
    Route::post('/examinations', [ExaminationController::class, 'store']);
    Route::apiResource('medicines', MedicineController::class);
});