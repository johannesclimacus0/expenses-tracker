<?php

use App\Http\Controllers\Categories\CategoryController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Transactions\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', CategoryController::class)
        ->only(['index', 'store', 'edit', 'update', 'destroy']);

    Route::resource('transactions', TransactionController::class)
        ->except('show');
});

Route::redirect('/', '/dashboard');
