<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('check-user', [AuthController::class, 'checkuser']);

Route::post('check-otp', [AuthController::class, 'checkOtp']);

Route::post('set-info', [AuthController::class, 'setPassword']);

Route::post('ccheck-auth', [AuthController::class, 'checkauth']);
