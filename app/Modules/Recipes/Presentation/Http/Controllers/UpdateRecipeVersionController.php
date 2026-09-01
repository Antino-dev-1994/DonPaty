<?php

namespace App\Modules\Recipes\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Recipes\Application\Data\RecipeData;
use App\Modules\Recipes\Application\UpdateRecipeDraft;
use App\Modules\Recipes\Domain\Models\Recipe;
use App\Modules\Recipes\Domain\Models\RecipeVersion;
use App\Modules\Recipes\Presentation\Http\Requests\SaveRecipeRequest;
use Illuminate\Http\RedirectResponse;

class UpdateRecipeVersionController extends Controller
{
    public function __invoke(SaveRecipeRequest $request, Recipe $recipe, RecipeVersion $version, UpdateRecipeDraft $action): RedirectResponse
    {
        $action->execute($recipe, $version, RecipeData::fromArray($request->validated()), $request->user());
        return to_route('recipes.show', $recipe)->with('success', 'Borrador actualizado correctamente.');
    }
}
