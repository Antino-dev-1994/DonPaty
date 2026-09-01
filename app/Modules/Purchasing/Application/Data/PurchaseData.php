<?php

namespace App\Modules\Purchasing\Application\Data;

use App\Models\User;
use App\Modules\Purchasing\Domain\Enums\PurchasePaymentCondition;
use Carbon\CarbonInterface;

final readonly class PurchaseData
{
    /** @param list<PurchaseLineData> $lines */
    public function __construct(
        public string $supplierPersonId,
        public ?string $supplierDocumentNumber,
        public CarbonInterface $issuedAt,
        public ?CarbonInterface $dueAt,
        public PurchasePaymentCondition $paymentCondition,
        public int $additionalCosts,
        public ?string $notes,
        public User $creator,
        public array $lines,
    ) {}
}
