<?php

use App\Modules\Pricing\Presentation\Http\Controllers\CreatePriceListController;
use App\Modules\Pricing\Presentation\Http\Controllers\EditPriceListController;
use App\Modules\Pricing\Presentation\Http\Controllers\ListPriceListsController;
use App\Modules\Pricing\Presentation\Http\Controllers\StorePriceListController;
use App\Modules\Pricing\Presentation\Http\Controllers\UpdatePriceListController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web','auth','verified'])->prefix('pricing')->name('pricing.')->group(function (): void { Route::get('/',ListPriceListsController::class)->name('index'); Route::get('/create',CreatePriceListController::class)->name('create'); Route::post('/',StorePriceListController::class)->name('store'); Route::get('/{priceList}/edit',EditPriceListController::class)->name('edit'); Route::put('/{priceList}',UpdatePriceListController::class)->name('update'); });
