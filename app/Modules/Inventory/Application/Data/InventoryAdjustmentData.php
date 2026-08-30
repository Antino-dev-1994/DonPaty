<?php

namespace App\Modules\Inventory\Application\Data;

use App\Models\User;
use Carbon\CarbonInterface;

final readonly class InventoryAdjustmentData
{
    /** @param list<array{presentation_id: string, counted_quantity: string, unit_cost: int|null}> $lines */
    public function __construct(
        public string $type,
        public CarbonInterface $effectiveAt,
        public string $reason,
        public User $creator,
        public array $lines,
    ) {}
}
