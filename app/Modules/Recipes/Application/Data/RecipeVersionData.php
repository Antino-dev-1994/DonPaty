<?php

namespace App\Modules\Recipes\Application\Data;

final readonly class RecipeVersionData
{
    /** @param list<RecipeIngredientData> $ingredients @param list<CompatibleProductData> $compatibleProducts @param list<RecipeBatchComponentData> $batchComponents */
    public function __construct(public string $referenceFlourQuantity, public string $referenceFlourUnitId, public string $expectedDoughYield, public string $yieldUnitId, public string $expectedWastePercentage, public ?string $instructions, public array $ingredients, public array $compatibleProducts, public array $batchComponents = []) {}
    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self((string) $data['reference_flour_quantity'], $data['reference_flour_unit_id'], (string) $data['expected_dough_yield'], $data['yield_unit_id'], (string) $data['expected_waste_percentage'], $data['instructions'] ?? null, array_map(fn (array $line, int $index) => RecipeIngredientData::fromArray($line, $index), $data['ingredients'], array_keys($data['ingredients'])), array_map(CompatibleProductData::fromArray(...), $data['compatible_products']), array_map(fn (array $line, int $index) => RecipeBatchComponentData::fromArray($line, $index), $data['batch_components'] ?? [], array_keys($data['batch_components'] ?? [])));
    }
}
