<?php

namespace App\Modules\Finance\Application\Data;

use App\Models\User;
use Carbon\CarbonInterface;

final readonly class SupplierPaymentData
{
    public function __construct(
        public int $amount,
        public string $financialAccountId,
        public CarbonInterface $paidAt,
        public User $creator,
        public ?string $reference = null,
    ) {}
}
