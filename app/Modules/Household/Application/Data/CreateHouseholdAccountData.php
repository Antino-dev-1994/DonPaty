<?php

namespace App\Modules\Household\Application\Data;

use App\Models\User;
use App\Modules\Finance\Domain\Enums\FinancialScope;

final readonly class CreateHouseholdAccountData
{
    public function __construct(
        public string $name,
        public FinancialScope $scope,
        public ?string $personId,
        public int $openingBalance,
        public User $creator,
    ) {}
}
