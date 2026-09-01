<?php

namespace App\Modules\Recipes\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Recipes\Domain\Models\Recipe;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowRecipeController extends Controller
{
    public function __invoke(Request $request, Recipe $recipe): Response
    {
        abort_unless($request->user()->hasPermission('recipes.view'), 403);
        $recipe->load(['versions' => fn ($query) => $query->withCount(['ingredients', 'compatibleProducts'])->latest('version_number')]);
        return Inertia::render('recipes/Show', ['recipe' => [...$recipe->only(['id', 'code', 'name', 'description', 'is_active']), 'versions' => $recipe->versions->map(fn ($version) => [...$version->only(['id', 'version_number', 'reference_flour_quantity', 'expected_dough_yield', 'expected_waste_percentage', 'ingredients_count', 'compatible_products_count']), 'status' => $version->status->value, 'status_label' => $version->status->label(), 'effective_from' => $version->effective_from?->toDateString(), 'effective_to' => $version->effective_to?->toDateString()])], 'massUnits' => Unit::query()->where('dimension', 'mass')->where('is_active', true)->get(['id', 'code', 'name']), 'today' => now()->toDateString(), 'canManage' => $request->user()->hasPermission('recipes.manage'), 'canActivate' => $request->user()->hasPermission('recipes.activate-version')]);
    }
}
