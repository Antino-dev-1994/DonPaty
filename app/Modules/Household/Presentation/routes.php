<?php

use App\Modules\Household\Presentation\Http\Controllers\HouseholdDashboardController;
use App\Modules\Household\Presentation\Http\Controllers\StoreHouseholdAccountController;
use App\Modules\Household\Presentation\Http\Controllers\StoreHouseholdTransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('household')->name('household.')->group(function (): void {
    Route::get('/', HouseholdDashboardController::class)->name('index');
    Route::post('/accounts', StoreHouseholdAccountController::class)->name('accounts.store');
    Route::post('/transactions', StoreHouseholdTransactionController::class)->name('transactions.store');
});
