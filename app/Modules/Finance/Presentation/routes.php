<?php

use App\Modules\Finance\Presentation\Http\Controllers\CreateSupplierPaymentController;
use App\Modules\Finance\Presentation\Http\Controllers\StoreSupplierPaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('finance')->name('finance.')->group(function (): void {
    Route::get('/payables/{payable}/payments/create', CreateSupplierPaymentController::class)->name('payables.payments.create');
    Route::post('/payables/{payable}/payments', StoreSupplierPaymentController::class)->name('payables.payments.store');
});
