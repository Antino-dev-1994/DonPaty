<?php

namespace App\Modules\Recipes\Application\Data;

final readonly class FinishingComponentData
{
    public function __construct(public string $itemId, public string $quantityPerUnit, public string $unitId) {}
    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self { return new self($data['item_id'], (string) $data['quantity_per_unit'], $data['unit_id']); }
}
