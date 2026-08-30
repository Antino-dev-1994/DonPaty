<?php

use App\Modules\People\Presentation\Http\Controllers\CreatePersonController;
use App\Modules\People\Presentation\Http\Controllers\EditPersonController;
use App\Modules\People\Presentation\Http\Controllers\ListPeopleController;
use App\Modules\People\Presentation\Http\Controllers\StorePersonController;
use App\Modules\People\Presentation\Http\Controllers\UpdatePersonController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('people')->name('people.')->group(function (): void {
    Route::get('/', ListPeopleController::class)->name('index');
    Route::get('/create', CreatePersonController::class)->name('create');
    Route::post('/', StorePersonController::class)->name('store');
    Route::get('/{person}/edit', EditPersonController::class)->name('edit');
    Route::put('/{person}', UpdatePersonController::class)->name('update');
});
