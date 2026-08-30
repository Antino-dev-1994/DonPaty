<?php

namespace App\Modules\Inventory\Application\Data;

use App\Models\User;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

final readonly class InventoryMovementData
{
    /** @param list<InventoryMovementLineData> $lines */
    public function __construct(
        public InventoryMovementType $type,
        public Carbon $effectiveAt,
        public User $creator,
        public array $lines,
        public ?Model $source = null,
        public ?string $notes = null,
        public ?AuthorizationRequest $authorization = null,
        public ?string $reversalOfId = null,
    ) {}
}
