<?php

use App\Modules\Catalog\Presentation\Http\Controllers\CreateItemController;
use App\Modules\Catalog\Presentation\Http\Controllers\CreatePresentationController;
use App\Modules\Catalog\Presentation\Http\Controllers\EditItemController;
use App\Modules\Catalog\Presentation\Http\Controllers\EditPresentationController;
use App\Modules\Catalog\Presentation\Http\Controllers\ListItemsController;
use App\Modules\Catalog\Presentation\Http\Controllers\ListUnitsController;
use App\Modules\Catalog\Presentation\Http\Controllers\StoreItemController;
use App\Modules\Catalog\Presentation\Http\Controllers\StorePresentationController;
use App\Modules\Catalog\Presentation\Http\Controllers\StoreUnitController;
use App\Modules\Catalog\Presentation\Http\Controllers\StoreUnitConversionController;
use App\Modules\Catalog\Presentation\Http\Controllers\SyncPackageComponentsController;
use App\Modules\Catalog\Presentation\Http\Controllers\UpdateItemController;
use App\Modules\Catalog\Presentation\Http\Controllers\UpdatePresentationController;
use App\Modules\Catalog\Presentation\Http\Controllers\UpdateUnitController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('catalog')->name('catalog.')->group(function (): void {
    Route::get('/units', ListUnitsController::class)->name('units.index');
    Route::post('/units', StoreUnitController::class)->name('units.store');
    Route::put('/units/{unit}', UpdateUnitController::class)->name('units.update');
    Route::post('/unit-conversions', StoreUnitConversionController::class)->name('unit-conversions.store');

    Route::get('/items', ListItemsController::class)->name('items.index');
    Route::get('/items/create', CreateItemController::class)->name('items.create');
    Route::post('/items', StoreItemController::class)->name('items.store');
    Route::get('/items/{item}/edit', EditItemController::class)->name('items.edit');
    Route::put('/items/{item}', UpdateItemController::class)->name('items.update');

    Route::get('/items/{item}/presentations/create', CreatePresentationController::class)->name('presentations.create');
    Route::post('/items/{item}/presentations', StorePresentationController::class)->name('presentations.store');
    Route::get('/items/{item}/presentations/{presentation}/edit', EditPresentationController::class)->name('presentations.edit');
    Route::put('/items/{item}/presentations/{presentation}', UpdatePresentationController::class)->name('presentations.update');
    Route::put('/items/{item}/presentations/{presentation}/components', SyncPackageComponentsController::class)->name('presentations.components.update');
});
