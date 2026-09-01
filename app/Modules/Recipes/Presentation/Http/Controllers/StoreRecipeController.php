<?php

namespace App\Modules\Recipes\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Recipes\Application\CreateRecipe;
use App\Modules\Recipes\Application\Data\RecipeData;
use App\Modules\Recipes\Presentation\Http\Requests\SaveRecipeRequest;
use Illuminate\Http\RedirectResponse;

class StoreRecipeController extends Controller
{
    public function __invoke(SaveRecipeRequest $request, CreateRecipe $action): RedirectResponse
    {
        $recipe = $action->execute(RecipeData::fromArray($request->validated()), $request->user());
        return to_route('recipes.show', $recipe)->with('success', 'Receta y versión borrador creadas.');
    }
}
