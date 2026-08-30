<?php

use App\Modules\Audit\Presentation\Http\Controllers\ListAuditEventsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->get('/audit', ListAuditEventsController::class)->name('audit.index');
