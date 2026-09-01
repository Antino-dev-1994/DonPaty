<?php

use App\Modules\Finance\Presentation\Http\Controllers\CreateBusinessRecordController;
use App\Modules\Finance\Presentation\Http\Controllers\CreateSupplierPaymentController;
use App\Modules\Finance\Presentation\Http\Controllers\FinanceDashboardController;
use App\Modules\Finance\Presentation\Http\Controllers\StoreExpenseRecordController;
use App\Modules\Finance\Presentation\Http\Controllers\StoreIncomeRecordController;
use App\Modules\Finance\Presentation\Http\Controllers\StoreSupplierPaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('finance')->name('finance.')->group(function (): void {
    Route::get('/', FinanceDashboardController::class)->name('index');
    Route::get('/records/create', CreateBusinessRecordController::class)->name('records.create');
    Route::post('/incomes', StoreIncomeRecordController::class)->name('incomes.store');
    Route::post('/expenses', StoreExpenseRecordController::class)->name('expenses.store');
    Route::get('/payables/{payable}/payments/create', CreateSupplierPaymentController::class)->name('payables.payments.create');
    Route::post('/payables/{payable}/payments', StoreSupplierPaymentController::class)->name('payables.payments.store');
});
