<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

// Handle the callback return from PayPal after payment execution
Route::get('/api/payment/success', [PaymentController::class, 'handleReturn']);

Route::get('{any?}', function () {
    return view('welcome'); 
})->where('any', '.*');