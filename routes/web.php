<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
// Route::post('/login', [AuthController::class, 'login']);
