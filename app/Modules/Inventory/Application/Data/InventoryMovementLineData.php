<?php

namespace App\Modules\Inventory\Application\Data;

final readonly class InventoryMovementLineData
{
    public function __construct(
        public string $presentationId,
        public string $quantityIn,
        public string $quantityOut,
        public ?int $unitCost = null,
        public ?int $totalCost = null,
    ) {}

    public static function incoming(string $presentationId, string $quantity, ?int $unitCost, ?int $totalCost = null): self
    {
        return new self($presentationId, $quantity, '0', $unitCost, $totalCost);
    }

    public static function outgoing(string $presentationId, string $quantity, ?int $totalCost = null): self
    {
        return new self($presentationId, '0', $quantity, null, $totalCost);
    }
}
