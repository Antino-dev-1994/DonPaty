<?php

use App\Modules\Launch\Presentation\Http\Controllers\ActivateLaunchController;
use App\Modules\Launch\Presentation\Http\Controllers\ShowLaunchController;
use App\Modules\Launch\Presentation\Http\Controllers\UpdateLaunchController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('launch')->name('launch.')->group(function (): void {
    Route::get('/', ShowLaunchController::class)->name('show');
    Route::put('/', UpdateLaunchController::class)->name('update');
    Route::post('/activate', ActivateLaunchController::class)->name('activate');
});
