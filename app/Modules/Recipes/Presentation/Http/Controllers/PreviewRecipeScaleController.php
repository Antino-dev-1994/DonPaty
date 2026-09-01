<?php

namespace App\Modules\Recipes\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Recipes\Application\ScaleRecipeVersion;
use App\Modules\Recipes\Domain\Models\Recipe;
use App\Modules\Recipes\Domain\Models\RecipeVersion;
use App\Modules\Recipes\Presentation\Http\Requests\ScaleRecipeRequest;
use Illuminate\Http\JsonResponse;

class PreviewRecipeScaleController extends Controller
{
    public function __invoke(ScaleRecipeRequest $request, Recipe $recipe, RecipeVersion $version, ScaleRecipeVersion $action): JsonResponse
    {
        abort_unless($version->recipe_id === $recipe->id, 404); $data = $request->validated();
        return response()->json($action->execute($version, (string) $data['flour_quantity'], Unit::query()->findOrFail($data['flour_unit_id'])));
    }
}
