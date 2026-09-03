<?php

use App\Modules\Dashboard\Presentation\Http\Controllers\ShowDashboardController;
use App\Modules\Dashboard\Presentation\Http\Controllers\ShowReportsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->get('/dashboard', ShowDashboardController::class)
    ->name('dashboard');

Route::middleware(['web', 'auth', 'verified'])
    ->get('/reports', ShowReportsController::class)
    ->name('reports.index');
