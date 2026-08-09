<?php

use App\Http\Controllers\Budgets\BudgetController;
use App\Http\Controllers\Categories\CategoryController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Goals\GoalContributionController;
use App\Http\Controllers\Goals\GoalController;
use App\Http\Controllers\RecurringTransactions\RecurringTransactionController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Transactions\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'index'])
        ->name('settings.index');
    Route::patch('/settings', [SettingsController::class, 'update'])
        ->name('settings.update');
    Route::patch('/settings/profile', [SettingsController::class, 'updateProfile'])
        ->name('settings.profile.update');
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])
        ->name('settings.password.update');
    Route::post('/settings/telegram/token', [SettingsController::class, 'createTelegramToken'])
        ->name('settings.telegram.token');
    Route::delete('/settings/telegram', [SettingsController::class, 'destroyTelegram'])
        ->name('settings.telegram.destroy');

    Route::resource('categories', CategoryController::class)
        ->only(['index', 'store', 'edit', 'update', 'destroy']);

    Route::post('/transactions/import', [TransactionController::class, 'importCsv'])
        ->name('transactions.import');
    Route::get('/transactions/export', [TransactionController::class, 'exportCsv'])
        ->name('transactions.export');
    Route::resource('transactions', TransactionController::class)
        ->except('show');

    Route::resource('recurring-transactions', RecurringTransactionController::class)
        ->except('show');

    Route::resource('budgets', BudgetController::class)
        ->except('show');

    Route::resource('goals', GoalController::class);
    Route::scopeBindings()->group(function (): void {
        Route::post('/goals/{goal}/contributions', [GoalContributionController::class, 'store'])
            ->name('goals.contributions.store');
        Route::delete('/goals/{goal}/contributions/{contribution}', [GoalContributionController::class, 'destroy'])
            ->name('goals.contributions.destroy');
    });
});

Route::redirect('/', '/dashboard');
