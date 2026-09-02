<?php

namespace App\Modules\Household\Application\Data;

use App\Models\User;
use Carbon\CarbonInterface;

final readonly class PayFundRequestData
{
    public function __construct(
        public string $sourceAccountId,
        public string $destinationAccountId,
        public CarbonInterface $paidAt,
        public User $payer,
    ) {}
}
