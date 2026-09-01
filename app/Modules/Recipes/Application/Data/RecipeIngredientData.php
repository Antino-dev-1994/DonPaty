<?php

namespace App\Modules\Recipes\Application\Data;

use App\Modules\Recipes\Domain\Enums\IngredientRole;

final readonly class RecipeIngredientData
{
    public function __construct(public string $itemId, public ?string $presentationId, public IngredientRole $role, public string $quantity, public string $unitId, public ?string $bakerPercentage, public bool $allowsSubstitution, public int $sortOrder) {}
    /** @param array<string, mixed> $data */
    public static function fromArray(array $data, int $index): self
    {
        return new self($data['item_id'], $data['presentation_id'] ?? null, IngredientRole::from($data['ingredient_role']), (string) $data['quantity'], $data['unit_id'], isset($data['baker_percentage']) ? (string) $data['baker_percentage'] : null, (bool) ($data['allows_substitution'] ?? false), $index);
    }
}
