<?php

namespace App\Modules\Recipes\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Recipes\Domain\Enums\RecipeVersionStatus;
use App\Modules\Recipes\Domain\Models\Recipe;
use App\Modules\Recipes\Domain\Models\RecipeVersion;
use DomainException;
use Illuminate\Support\Facades\DB;

class CreateNextRecipeVersion
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(Recipe $recipe, User $creator): RecipeVersion
    {
        return DB::transaction(function () use ($recipe, $creator): RecipeVersion {
            $recipe = Recipe::query()->lockForUpdate()->findOrFail($recipe->id);
            if ($recipe->versions()->where('status', RecipeVersionStatus::Draft)->exists()) throw new DomainException('Ya existe una versión en borrador.');
            $source = $recipe->versions()->with(['ingredients', 'compatibleProducts.finishingComponents'])->latest('version_number')->firstOrFail();
            $version = $recipe->versions()->create(['version_number' => $source->version_number + 1, 'status' => RecipeVersionStatus::Draft, 'reference_flour_quantity' => $source->reference_flour_quantity, 'reference_flour_unit_id' => $source->reference_flour_unit_id, 'expected_dough_yield' => $source->expected_dough_yield, 'yield_unit_id' => $source->yield_unit_id, 'expected_waste_percentage' => $source->expected_waste_percentage, 'instructions' => $source->instructions, 'created_by' => $creator->id]);
            foreach ($source->ingredients as $line) $version->ingredients()->create($line->only(['item_id', 'presentation_id', 'ingredient_role', 'quantity', 'unit_id', 'baker_percentage', 'allows_substitution', 'sort_order']));
            foreach ($source->compatibleProducts as $line) {
                $product = $version->compatibleProducts()->create($line->only(['presentation_id', 'dough_weight_per_unit', 'dough_weight_unit_id', 'baking_loss_percentage', 'cost_weight_factor']));
                foreach ($line->finishingComponents as $component) $product->finishingComponents()->create($component->only(['item_id', 'quantity_per_unit', 'unit_id']));
            }
            $this->audit->execute('recipes.version_created', $version, $creator, after: $version->load(['ingredients', 'compatibleProducts.finishingComponents'])->toArray());

            return $version;
        });
    }
}
