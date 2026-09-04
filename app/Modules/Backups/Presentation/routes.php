<?php

use App\Modules\Backups\Presentation\Http\Controllers\DownloadBackupController;
use App\Modules\Backups\Presentation\Http\Controllers\ListBackupsController;
use App\Modules\Backups\Presentation\Http\Controllers\StoreBackupController;
use App\Modules\Backups\Presentation\Http\Controllers\VerifyBackupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('backups')->name('backups.')->group(function (): void {
    Route::get('/', ListBackupsController::class)->name('index');
    Route::post('/', StoreBackupController::class)->name('store');
    Route::post('/{backup}/verify', VerifyBackupController::class)->name('verify');
    Route::get('/{backup}/download', DownloadBackupController::class)->name('download');
});
