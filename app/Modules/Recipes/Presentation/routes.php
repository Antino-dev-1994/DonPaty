<?php

use App\Modules\Recipes\Presentation\Http\Controllers\CreateNextRecipeVersionController;
use App\Modules\Recipes\Presentation\Http\Controllers\CreateRecipeController;
use App\Modules\Recipes\Presentation\Http\Controllers\EditRecipeVersionController;
use App\Modules\Recipes\Presentation\Http\Controllers\ListRecipesController;
use App\Modules\Recipes\Presentation\Http\Controllers\PreviewRecipeScaleController;
use App\Modules\Recipes\Presentation\Http\Controllers\PublishRecipeVersionController;
use App\Modules\Recipes\Presentation\Http\Controllers\ShowRecipeController;
use App\Modules\Recipes\Presentation\Http\Controllers\StoreRecipeController;
use App\Modules\Recipes\Presentation\Http\Controllers\UpdateRecipeVersionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('recipes')->name('recipes.')->group(function (): void {
    Route::get('/', ListRecipesController::class)->name('index'); Route::get('/create', CreateRecipeController::class)->name('create'); Route::post('/', StoreRecipeController::class)->name('store'); Route::get('/{recipe}', ShowRecipeController::class)->name('show');
    Route::post('/{recipe}/versions', CreateNextRecipeVersionController::class)->name('versions.store'); Route::get('/{recipe}/versions/{version}/edit', EditRecipeVersionController::class)->name('versions.edit'); Route::put('/{recipe}/versions/{version}', UpdateRecipeVersionController::class)->name('versions.update'); Route::post('/{recipe}/versions/{version}/publish', PublishRecipeVersionController::class)->name('versions.publish'); Route::get('/{recipe}/versions/{version}/scale', PreviewRecipeScaleController::class)->name('versions.scale');
});
