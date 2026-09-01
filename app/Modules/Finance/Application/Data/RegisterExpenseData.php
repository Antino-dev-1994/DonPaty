<?php

namespace App\Modules\Finance\Application\Data;

use App\Models\User;
use Carbon\CarbonInterface;

final readonly class RegisterExpenseData
{
    public function __construct(
        public string $categoryId,
        public ?string $personId,
        public ?string $costPeriodId,
        public ?string $costPoolEntryId,
        public CarbonInterface $effectiveAt,
        public ?CarbonInterface $dueAt,
        public string $description,
        public int $totalAmount,
        public int $initialPaymentAmount,
        public ?string $financialAccountId,
        public ?string $paymentReference,
        public User $creator,
    ) {}
}
