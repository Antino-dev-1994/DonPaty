<?php

namespace App\Modules\Household\Application\Data;

use App\Models\User;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use Carbon\CarbonInterface;

final readonly class CreateFundRequestData
{
    public function __construct(
        public string $requesterPersonId,
        public FinancialScope $sourceScope,
        public int $amount,
        public string $reason,
        public CarbonInterface $neededAt,
        public User $creator,
    ) {}
}
