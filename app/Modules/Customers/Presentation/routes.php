<?php

use App\Modules\Customers\Presentation\Http\Controllers\CreateCustomerController;
use App\Modules\Customers\Presentation\Http\Controllers\EditCustomerController;
use App\Modules\Customers\Presentation\Http\Controllers\ListCustomersController;
use App\Modules\Customers\Presentation\Http\Controllers\ShowCustomerController;
use App\Modules\Customers\Presentation\Http\Controllers\StoreCustomerController;
use App\Modules\Customers\Presentation\Http\Controllers\UpdateCustomerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web','auth','verified'])->prefix('customers')->name('customers.')->group(function (): void {
    Route::get('/', ListCustomersController::class)->name('index'); Route::get('/create', CreateCustomerController::class)->name('create'); Route::post('/', StoreCustomerController::class)->name('store'); Route::get('/{customer}', ShowCustomerController::class)->name('show'); Route::get('/{customer}/edit', EditCustomerController::class)->name('edit'); Route::put('/{customer}', UpdateCustomerController::class)->name('update');
});
