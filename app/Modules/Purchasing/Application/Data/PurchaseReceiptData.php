<?php

namespace App\Modules\Purchasing\Application\Data;

use App\Models\User;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use Carbon\CarbonInterface;

final readonly class PurchaseReceiptData
{
    /** @param list<PurchaseReceiptLineData> $lines */
    public function __construct(
        public CarbonInterface $receivedAt,
        public User $receiver,
        public array $lines,
        public ?string $notes = null,
        public ?AuthorizationRequest $authorization = null,
    ) {}
}
