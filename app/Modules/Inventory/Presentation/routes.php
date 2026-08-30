<?php

use App\Modules\Inventory\Presentation\Http\Controllers\ConfirmAdjustmentController;
use App\Modules\Inventory\Presentation\Http\Controllers\CreateAdjustmentController;
use App\Modules\Inventory\Presentation\Http\Controllers\InventoryDashboardController;
use App\Modules\Inventory\Presentation\Http\Controllers\ListAdjustmentsController;
use App\Modules\Inventory\Presentation\Http\Controllers\ListMovementsController;
use App\Modules\Inventory\Presentation\Http\Controllers\PackageConversionsController;
use App\Modules\Inventory\Presentation\Http\Controllers\RequestAdjustmentAuthorizationController;
use App\Modules\Inventory\Presentation\Http\Controllers\ShowAdjustmentController;
use App\Modules\Inventory\Presentation\Http\Controllers\StoreAdjustmentController;
use App\Modules\Inventory\Presentation\Http\Controllers\StorePackageConversionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('inventory')->name('inventory.')->group(function (): void {
    Route::get('/', InventoryDashboardController::class)->name('index');
    Route::get('/movements', ListMovementsController::class)->name('movements.index');
    Route::get('/adjustments', ListAdjustmentsController::class)->name('adjustments.index');
    Route::get('/adjustments/create', CreateAdjustmentController::class)->name('adjustments.create');
    Route::post('/adjustments', StoreAdjustmentController::class)->name('adjustments.store');
    Route::get('/adjustments/{adjustment}', ShowAdjustmentController::class)->name('adjustments.show');
    Route::post('/adjustments/{adjustment}/authorization', RequestAdjustmentAuthorizationController::class)->name('adjustments.authorization');
    Route::post('/adjustments/{adjustment}/confirm', ConfirmAdjustmentController::class)->name('adjustments.confirm');
    Route::get('/packages', PackageConversionsController::class)->name('packages.index');
    Route::post('/packages', StorePackageConversionController::class)->name('packages.store');
});
