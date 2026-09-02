<?php

namespace App\Modules\Household\Application\Data;

use App\Models\User;
use Carbon\CarbonInterface;

final readonly class PayDebtData
{
    public function __construct(
        public int $amount,
        public string $financialAccountId,
        public CarbonInterface $paidAt,
        public User $actor,
    ) {}
}
