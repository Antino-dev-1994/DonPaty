<?php

namespace App\Modules\Household\Application\Data;

use App\Models\User;
use Carbon\CarbonInterface;

final readonly class ContributeToSavingsGoalData
{
    public function __construct(
        public string $sourceAccountId,
        public int $amount,
        public CarbonInterface $contributedAt,
        public User $actor,
    ) {}
}
