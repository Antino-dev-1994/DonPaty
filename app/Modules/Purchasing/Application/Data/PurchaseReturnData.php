<?php

namespace App\Modules\Purchasing\Application\Data;

use App\Models\User;
use Carbon\CarbonInterface;

final readonly class PurchaseReturnData
{
    /** @param list<PurchaseReturnLineData> $lines */
    public function __construct(public CarbonInterface $returnedAt, public string $reason, public User $creator, public array $lines) {}
}
