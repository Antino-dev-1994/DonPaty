<?php

namespace App\Modules\Household\Application\Data;

use App\Models\User;
use App\Modules\Household\Domain\Enums\DebtDirection;
use Carbon\CarbonInterface;

final readonly class CreateDebtData
{
    public function __construct(
        public DebtDirection $direction,
        public ?string $personId,
        public string $description,
        public int $principalAmount,
        public float $annualInterestRate,
        public CarbonInterface $startDate,
        public CarbonInterface $firstDueAt,
        public int $installmentCount,
        public string $financialAccountId,
        public User $creator,
    ) {}
}
