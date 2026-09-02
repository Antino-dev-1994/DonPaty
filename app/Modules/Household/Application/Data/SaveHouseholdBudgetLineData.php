<?php

namespace App\Modules\Household\Application\Data;

use App\Models\User;

final readonly class SaveHouseholdBudgetLineData
{
    public function __construct(
        public int $year,
        public int $month,
        public string $categoryId,
        public ?string $personId,
        public int $budgetedAmount,
        public User $actor,
    ) {}
}
