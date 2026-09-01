<?php

namespace App\Modules\CostAccounting\Application\Data;

use App\Models\User;

final readonly class OpenCostPeriodData
{
    /** @param list<UtilityCostData> $utilities */
    public function __construct(
        public int $year,
        public int $month,
        public User $opener,
        public array $utilities,
        public int $standardLaborRatePerKg = 0,
        public ?string $laborRateReason = null,
    ) {}
}
