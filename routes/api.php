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
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\PrescriptionItemController;
use App\Http\Controllers\InvoiceController;

// Public route for authentication
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', EnsurePermission::class])->group(function () {
    Route::patch('users/{user}/status', [UserController::class, 'updateStatus']);
    Route::apiResource('users', UserController::class);
    
    Route::apiResource('specialties', SpecialtyController::class);
    Route::apiResource('doctors', DoctorController::class);
    Route::apiResource('patients', PatientController::class);
    
    Route::patch('appointments/{id}/status', [AppointmentController::class, 'changeStatus']);
    Route::apiResource('appointments', AppointmentController::class);

    Route::apiResource('examinations', ExaminationController::class);
    
    Route::patch('medicines/{id}/stock', [MedicineController::class, 'adjustStock']);
    Route::apiResource('medicines', MedicineController::class);

    Route::get('/prescriptions', [PrescriptionController::class, 'index'])
        ->middleware('permission:PRESCRIPTIONS.FINDALL');
        
    Route::get('/prescriptions/{id}', [PrescriptionController::class, 'show'])
        ->middleware('permission:PRESCRIPTIONS.FINDONE');

    Route::post('/prescriptions', [PrescriptionController::class, 'store'])
        ->middleware('permission:PRESCRIPTIONS.CREATE');

    Route::post('/prescriptions/{prescription}/items', [PrescriptionItemController::class, 'store'])
        ->middleware('permission:PRESCRIPTIONS.ADDITEM');
        
    Route::patch('/prescription-items/{prescriptionItem}', [PrescriptionItemController::class, 'update'])
        ->middleware('permission:PRESCRIPTIONS.UPDATEITEM');
        
    Route::delete('/prescription-items/{prescriptionItem}', [PrescriptionItemController::class, 'destroy'])
        ->middleware('permission:PRESCRIPTIONS.REMOVEITEM');

    

    Route::post('/invoices', [InvoiceController::class, 'store'])
        ->middleware('permission:INVOICES.CREATE');

    Route::patch('/invoices/{id}/discount', [InvoiceController::class, 'update'])
        ->middleware('permission:INVOICES.UPDATE');
        
    Route::patch('/invoices/{id}/cancel', [InvoiceController::class, 'updateStatus'])
        ->middleware('permission:INVOICES.UPDATESTATUS');
});