<?php

namespace App\Modules\Recipes\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Recipes\Application\PublishRecipeVersion;
use App\Modules\Recipes\Domain\Models\Recipe;
use App\Modules\Recipes\Domain\Models\RecipeVersion;
use App\Modules\Recipes\Presentation\Http\Requests\PublishRecipeVersionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class PublishRecipeVersionController extends Controller
{
    public function __invoke(PublishRecipeVersionRequest $request, Recipe $recipe, RecipeVersion $version, PublishRecipeVersion $action): RedirectResponse
    {
        abort_unless($version->recipe_id === $recipe->id, 404); $action->execute($version, Carbon::parse($request->validated('effective_from')), $request->user());
        return back()->with('success', 'Versión publicada con su nueva vigencia.');
    }
}
