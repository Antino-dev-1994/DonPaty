<?php

namespace App\Modules\Recipes\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Recipes\Application\Data\RecipeData;
use App\Modules\Recipes\Domain\Enums\RecipeVersionStatus;
use App\Modules\Recipes\Domain\Models\Recipe;
use Illuminate\Support\Facades\DB;

class CreateRecipe
{
    public function __construct(private readonly SaveDraftRecipeVersion $saveVersion, private readonly RecordAuditEvent $audit) {}

    public function execute(RecipeData $data, User $creator): Recipe
    {
        return DB::transaction(function () use ($data, $creator): Recipe {
            $recipe = Recipe::create(['code' => $data->code, 'name' => $data->name, 'description' => $data->description, 'is_active' => $data->isActive]);
            $version = $recipe->versions()->create(['version_number' => 1, 'status' => RecipeVersionStatus::Draft, 'reference_flour_quantity' => $data->version->referenceFlourQuantity, 'reference_flour_unit_id' => $data->version->referenceFlourUnitId, 'expected_dough_yield' => $data->version->expectedDoughYield, 'yield_unit_id' => $data->version->yieldUnitId, 'expected_waste_percentage' => $data->version->expectedWastePercentage, 'instructions' => $data->version->instructions, 'created_by' => $creator->id]);
            $this->saveVersion->execute($version, $data->version);
            $this->audit->execute('recipes.recipe_created', $recipe, $creator, after: $recipe->load('versions')->toArray());

            return $recipe;
        });
    }
}
