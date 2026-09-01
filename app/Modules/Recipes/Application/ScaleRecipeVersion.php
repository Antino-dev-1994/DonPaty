<?php

namespace App\Modules\Recipes\Application;

use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Catalog\Domain\Services\UnitConverter;
use App\Modules\Recipes\Domain\Models\RecipeVersion;
use DomainException;

class ScaleRecipeVersion
{
    public function __construct(private readonly UnitConverter $converter) {}

    /** @return array<string, mixed> */
    public function execute(RecipeVersion $version, string $flourQuantity, Unit $flourUnit): array
    {
        if (bccomp($flourQuantity, '0', 6) <= 0) throw new DomainException('La cantidad de harina debe ser positiva.');
        $version->loadMissing(['referenceFlourUnit', 'yieldUnit', 'ingredients.item', 'ingredients.unit', 'compatibleProducts.presentation.item', 'compatibleProducts.doughWeightUnit']);
        $targetInReferenceUnit = $this->converter->convert($flourQuantity, $flourUnit, $version->referenceFlourUnit);
        $factor = bcdiv($targetInReferenceUnit, $version->reference_flour_quantity, 8);
        $scaledYield = bcmul($version->expected_dough_yield, $factor, 6);

        return [
            'factor' => $factor, 'flour_quantity' => $flourQuantity, 'flour_unit' => $flourUnit->code,
            'expected_dough_yield' => $scaledYield, 'yield_unit' => $version->yieldUnit->code,
            'ingredients' => $version->ingredients->map(fn ($line) => ['item' => $line->item->name, 'quantity' => bcmul($line->quantity, $factor, 6), 'unit' => $line->unit->code, 'baker_percentage' => $line->baker_percentage]),
            'compatible_products' => $version->compatibleProducts->map(function ($product) use ($scaledYield, $version): array {
                $yieldInProductUnit = $this->converter->convert($scaledYield, $version->yieldUnit, $product->doughWeightUnit);
                return ['presentation_id' => $product->presentation_id, 'product' => "{$product->presentation->item->name} — {$product->presentation->name}", 'estimated_units' => bcdiv($yieldInProductUnit, $product->dough_weight_per_unit, 3), 'dough_weight_per_unit' => $product->dough_weight_per_unit, 'unit' => $product->doughWeightUnit->code];
            }),
        ];
    }
}
