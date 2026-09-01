<?php

namespace App\Modules\Recipes\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Recipes\Application\CreateNextRecipeVersion;
use App\Modules\Recipes\Domain\Models\Recipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CreateNextRecipeVersionController extends Controller
{
    public function __invoke(Request $request, Recipe $recipe, CreateNextRecipeVersion $action): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('recipes.manage'), 403); $version = $action->execute($recipe, $request->user());
        return to_route('recipes.versions.edit', [$recipe, $version])->with('success', 'Nueva versión borrador creada desde el historial.');
    }
}
