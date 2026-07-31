<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\EmailVerificationNotificationController;
use App\Http\Controllers\EmailVerificationPromptController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NewPasswordController;
use App\Http\Controllers\PasswordResetLinkController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'index')->name('login');
        Route::post('/login', 'store')->name('login.store')->middleware('throttle:login');
    });

    Route::controller(RegistrationController::class)->group(function () {
        Route::get('/register', 'index')->name('register');
        Route::post('/register', 'store')->name('register.store');
    });

    Route::controller(PasswordResetLinkController::class)->group(function () {
        Route::get('/forgot-password', 'index')
            ->name('password.request');

        Route::post('/forgot-password', 'store')
            ->name('password.email')
            ->middleware('throttle:password-reset-email');
    });

    Route::controller(NewPasswordController::class)->group(function () {
        Route::get('/reset-password/{token}', 'create')->name('password.reset');

        Route::post('/reset-password', 'store')->name('password.update');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', EmailVerificationPromptController::class)->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', EmailVerificationController::class)->name('verification.verify')
        ->middleware(['signed', 'throttle:6,1']);

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->name('verification.send')
        ->middleware('throttle:verification-email');

    Route::delete('/logout', [LoginController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::redirect('/', '/dashboard');
