<?php

use App\Modules\Purchasing\Presentation\Http\Controllers\CreatePurchaseController;
use App\Modules\Purchasing\Presentation\Http\Controllers\CreatePurchaseReceiptController;
use App\Modules\Purchasing\Presentation\Http\Controllers\CreateSupplierController;
use App\Modules\Purchasing\Presentation\Http\Controllers\EditSupplierController;
use App\Modules\Purchasing\Presentation\Http\Controllers\ListPurchasesController;
use App\Modules\Purchasing\Presentation\Http\Controllers\ListSuppliersController;
use App\Modules\Purchasing\Presentation\Http\Controllers\ShowPurchaseController;
use App\Modules\Purchasing\Presentation\Http\Controllers\StorePurchaseController;
use App\Modules\Purchasing\Presentation\Http\Controllers\StorePurchaseReceiptController;
use App\Modules\Purchasing\Presentation\Http\Controllers\StoreSupplierController;
use App\Modules\Purchasing\Presentation\Http\Controllers\UpdateSupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('purchasing')->name('purchasing.')->group(function (): void {
    Route::get('/suppliers', ListSuppliersController::class)->name('suppliers.index');
    Route::get('/suppliers/create', CreateSupplierController::class)->name('suppliers.create');
    Route::post('/suppliers', StoreSupplierController::class)->name('suppliers.store');
    Route::get('/suppliers/{supplier}/edit', EditSupplierController::class)->name('suppliers.edit');
    Route::put('/suppliers/{supplier}', UpdateSupplierController::class)->name('suppliers.update');

    Route::get('/purchases', ListPurchasesController::class)->name('purchases.index');
    Route::get('/purchases/create', CreatePurchaseController::class)->name('purchases.create');
    Route::post('/purchases', StorePurchaseController::class)->name('purchases.store');
    Route::get('/purchases/{purchase}', ShowPurchaseController::class)->name('purchases.show');
    Route::get('/purchases/{purchase}/receipts/create', CreatePurchaseReceiptController::class)->name('receipts.create');
    Route::post('/purchases/{purchase}/receipts', StorePurchaseReceiptController::class)->name('receipts.store');
});
