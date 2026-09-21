<?php

namespace App\Modules\Recipes\Application\Data;

use App\Modules\Recipes\Domain\Enums\RecipeBatchComponentType;

final readonly class RecipeBatchComponentData
{
    public function __construct(
        public RecipeBatchComponentType $type,
        public string $label,
        public ?string $itemId,
        public ?string $quantityPerBatch,
        public ?string $unitId,
        public ?int $amountPerBatch,
        public int $sortOrder,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data, int $sortOrder): self
    {
        return new self(
            RecipeBatchComponentType::from($data['type']),
            $data['label'],
            $data['item_id'] ?? null,
            isset($data['quantity_per_batch']) ? (string) $data['quantity_per_batch'] : null,
            $data['unit_id'] ?? null,
            isset($data['amount_per_batch']) ? (int) $data['amount_per_batch'] : null,
            $sortOrder,
        );
    }
}
