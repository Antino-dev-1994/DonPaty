<?php

namespace App\Modules\Household\Application\Data;

use App\Models\User;
use Carbon\CarbonInterface;

final readonly class CreateSavingsGoalData
{
    public function __construct(
        public string $name,
        public ?string $personId,
        public string $financialAccountId,
        public int $targetAmount,
        public ?CarbonInterface $targetDate,
        public User $creator,
    ) {}
}
