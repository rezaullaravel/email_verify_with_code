<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginRegisterController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('login-register',[LoginRegisterController::class,'index']);
Route::post('post-register',[LoginRegisterController::class,'postRegister'])->name('post.register');

//confirm user account
Route::get('user/confirm/{code}',[LoginRegisterController::class,'confirmUserAccount']);

// Display verification form
Route::get('/verify/{email}', [LoginRegisterController::class, 'showVerificationForm'])->name('verify.email');

// Handle verification
Route::post('/verify-email', [LoginRegisterController::class, 'verifyEmail'])->name('verify.email.submit');

// Resend verification code
Route::get('/resend-verification/{email}', [LoginRegisterController::class, 'resendVerificationCode'])->name('resend.verification');
