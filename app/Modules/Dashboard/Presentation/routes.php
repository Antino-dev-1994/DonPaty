<?php

use App\Modules\Dashboard\Presentation\Http\Controllers\ShowDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->get('/dashboard', ShowDashboardController::class)
    ->name('dashboard');
