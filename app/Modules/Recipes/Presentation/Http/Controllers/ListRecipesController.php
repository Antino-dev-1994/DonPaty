<?php

namespace App\Modules\Recipes\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Recipes\Domain\Models\Recipe;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListRecipesController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('recipes.view'), 403); $search = trim($request->string('search')->toString());
        return Inertia::render('recipes/Index', ['recipes' => Recipe::query()->withCount('versions')->when($search !== '', fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))->orderBy('name')->paginate(25)->withQueryString()->through(fn (Recipe $recipe) => [...$recipe->only(['id', 'code', 'name', 'is_active']), 'versions_count' => $recipe->versions_count, 'current_version' => $recipe->versions()->applicableOn(now()->toDateString())->value('version_number')]), 'filters' => ['search' => $search], 'canManage' => $request->user()->hasPermission('recipes.manage')]);
    }
}
