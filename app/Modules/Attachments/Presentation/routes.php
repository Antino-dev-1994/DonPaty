<?php

use App\Modules\Attachments\Presentation\Http\Controllers\DeleteAttachmentController;
use App\Modules\Attachments\Presentation\Http\Controllers\DownloadAttachmentController;
use App\Modules\Attachments\Presentation\Http\Controllers\StoreAttachmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('attachments')->name('attachments.')->group(function (): void {
    Route::post('/{type}/{id}', StoreAttachmentController::class)->name('store');
    Route::get('/{attachment}/download', DownloadAttachmentController::class)->name('download');
    Route::delete('/{attachment}', DeleteAttachmentController::class)->name('destroy');
});
