<?php

use App\Modules\Identity\Presentation\Http\Controllers\ActivateUserController;
use App\Modules\Identity\Presentation\Http\Controllers\BlockUserController;
use App\Modules\Identity\Presentation\Http\Controllers\CreateUserController;
use App\Modules\Identity\Presentation\Http\Controllers\DecideAuthorizationController;
use App\Modules\Identity\Presentation\Http\Controllers\EditRoleController;
use App\Modules\Identity\Presentation\Http\Controllers\EditUserController;
use App\Modules\Identity\Presentation\Http\Controllers\ListAuthorizationsController;
use App\Modules\Identity\Presentation\Http\Controllers\ListRolesController;
use App\Modules\Identity\Presentation\Http\Controllers\ListUsersController;
use App\Modules\Identity\Presentation\Http\Controllers\RevokeUserSessionController;
use App\Modules\Identity\Presentation\Http\Controllers\StoreUserController;
use App\Modules\Identity\Presentation\Http\Controllers\UpdateRolePermissionsController;
use App\Modules\Identity\Presentation\Http\Controllers\UpdateUserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->group(function (): void {
    Route::prefix('users')->name('users.')->group(function (): void {
        Route::get('/', ListUsersController::class)->name('index');
        Route::get('/create', CreateUserController::class)->name('create');
        Route::post('/', StoreUserController::class)->name('store');
        Route::get('/{managedUser}/edit', EditUserController::class)->name('edit');
        Route::put('/{managedUser}', UpdateUserController::class)->name('update');
        Route::post('/{managedUser}/block', BlockUserController::class)->name('block');
        Route::post('/{managedUser}/activate', ActivateUserController::class)->name('activate');
        Route::delete('/{managedUser}/sessions/{session}', RevokeUserSessionController::class)->name('sessions.destroy');
    });

    Route::prefix('roles')->name('roles.')->group(function (): void {
        Route::get('/', ListRolesController::class)->name('index');
        Route::get('/{role}/edit', EditRoleController::class)->name('edit');
        Route::put('/{role}/permissions', UpdateRolePermissionsController::class)->name('permissions.update');
    });

    Route::get('/authorizations', ListAuthorizationsController::class)->name('authorizations.index');
    Route::post('/authorizations/{authorization}/decision', DecideAuthorizationController::class)->name('authorizations.decide');
});
