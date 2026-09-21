<?php

namespace App\Modules\Recipes\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Recipes\Domain\Models\Recipe;
use App\Modules\Recipes\Domain\Models\RecipeVersion;
use App\Modules\Recipes\Presentation\Support\RecipeFormOptions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EditRecipeVersionController extends Controller
{
    public function __invoke(Request $request, Recipe $recipe, RecipeVersion $version, RecipeFormOptions $options): Response
    {
        abort_unless($request->user()->hasPermission('recipes.manage') && $version->recipe_id === $recipe->id && $version->isDraft(), 403);
        $version->load(['ingredients', 'batchComponents', 'compatibleProducts.finishingComponents']);
        return Inertia::render('recipes/Edit', [...$options->get(), 'recipe' => [...$recipe->only(['id', 'code', 'name', 'description', 'is_active']), 'version' => [...$version->only(['id', 'version_number', 'reference_flour_quantity', 'reference_flour_unit_id', 'expected_dough_yield', 'yield_unit_id', 'expected_waste_percentage', 'instructions']), 'ingredients' => $version->ingredients->map(fn ($line) => ['item_id' => $line->item_id, 'presentation_id' => $line->presentation_id, 'ingredient_role' => $line->ingredient_role->value, 'quantity' => $line->quantity, 'unit_id' => $line->unit_id, 'baker_percentage' => $line->baker_percentage, 'allows_substitution' => $line->allows_substitution]), 'batch_components' => $version->batchComponents->map(fn ($component) => ['type' => $component->type->value, 'label' => $component->label, 'item_id' => $component->item_id, 'quantity_per_batch' => $component->quantity_per_batch, 'unit_id' => $component->unit_id, 'amount_per_batch' => $component->amount_per_batch]), 'compatible_products' => $version->compatibleProducts->map(fn ($product) => ['presentation_id' => $product->presentation_id, 'dough_weight_per_unit' => $product->dough_weight_per_unit, 'dough_weight_unit_id' => $product->dough_weight_unit_id, 'baking_loss_percentage' => $product->baking_loss_percentage, 'cost_weight_factor' => $product->cost_weight_factor, 'finishing_components' => $product->finishingComponents->map(fn ($component) => $component->only(['item_id', 'quantity_per_unit', 'unit_id']))])]]]);
    }
}
