<?php

namespace App\Modules\Household\Application\Data;

use App\Models\User;
use App\Modules\Household\Domain\Enums\HouseholdTransactionType;
use Carbon\CarbonInterface;

final readonly class HouseholdTransactionData
{
    public function __construct(
        public HouseholdTransactionType $type,
        public ?string $personId,
        public ?string $categoryId,
        public ?string $fromAccountId,
        public ?string $toAccountId,
        public CarbonInterface $occurredAt,
        public int $amount,
        public string $description,
        public bool $countsForBudget,
        public User $creator,
    ) {}
}
