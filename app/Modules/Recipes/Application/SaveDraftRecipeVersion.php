<?php

namespace App\Modules\Recipes\Application;

use App\Modules\Recipes\Application\Data\RecipeVersionData;
use App\Modules\Recipes\Domain\Models\RecipeVersion;
use App\Modules\Recipes\Domain\Services\RecipeVersionValidator;
use DomainException;
use Illuminate\Support\Facades\DB;

class SaveDraftRecipeVersion
{
    public function __construct(private readonly RecipeVersionValidator $validator) {}

    public function execute(RecipeVersion $version, RecipeVersionData $data): RecipeVersion
    {
        if (! $version->isDraft()) throw new DomainException('Solo las versiones en borrador pueden modificarse.');
        $this->validator->validate($data);

        return DB::transaction(function () use ($version, $data): RecipeVersion {
            $version->update(['reference_flour_quantity' => $data->referenceFlourQuantity, 'reference_flour_unit_id' => $data->referenceFlourUnitId, 'expected_dough_yield' => $data->expectedDoughYield, 'yield_unit_id' => $data->yieldUnitId, 'expected_waste_percentage' => $data->expectedWastePercentage, 'instructions' => $data->instructions]);
            $version->ingredients()->delete();
            foreach ($data->ingredients as $line) $version->ingredients()->create(['item_id' => $line->itemId, 'presentation_id' => $line->presentationId, 'ingredient_role' => $line->role, 'quantity' => $line->quantity, 'unit_id' => $line->unitId, 'baker_percentage' => $line->bakerPercentage, 'allows_substitution' => $line->allowsSubstitution, 'sort_order' => $line->sortOrder]);
            $version->batchComponents()->delete();
            foreach ($data->batchComponents as $component) $version->batchComponents()->create(['type' => $component->type, 'label' => $component->label, 'item_id' => $component->itemId, 'quantity_per_batch' => $component->quantityPerBatch, 'unit_id' => $component->unitId, 'amount_per_batch' => $component->amountPerBatch, 'sort_order' => $component->sortOrder]);
            $version->compatibleProducts()->delete();
            foreach ($data->compatibleProducts as $line) {
                $product = $version->compatibleProducts()->create(['presentation_id' => $line->presentationId, 'dough_weight_per_unit' => $line->doughWeightPerUnit, 'dough_weight_unit_id' => $line->doughWeightUnitId, 'baking_loss_percentage' => $line->bakingLossPercentage, 'cost_weight_factor' => $line->costWeightFactor]);
                foreach ($line->finishingComponents as $component) $product->finishingComponents()->create(['item_id' => $component->itemId, 'quantity_per_unit' => $component->quantityPerUnit, 'unit_id' => $component->unitId]);
            }

            return $version->fresh(['ingredients', 'batchComponents', 'compatibleProducts.finishingComponents']);
        });
    }
}
