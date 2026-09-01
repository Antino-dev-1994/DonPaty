<?php

namespace App\Modules\Finance\Application;

use App\Modules\Finance\Domain\Enums\PayableStatus;
use App\Modules\Finance\Domain\Models\Payable;
use App\Modules\Purchasing\Domain\Models\Purchase;
use App\Modules\Shared\Application\NextDocumentNumber;

class CreatePayableForPurchase
{
    public function __construct(private readonly NextDocumentNumber $nextDocumentNumber) {}

    public function execute(Purchase $purchase): Payable
    {
        return Payable::create([
            'document_number' => $this->nextDocumentNumber->execute('payable', 'CXP', $purchase->issued_at),
            'person_id' => $purchase->supplier_person_id,
            'source_type' => $purchase->getMorphClass(), 'source_id' => $purchase->id,
            'issued_at' => $purchase->issued_at, 'due_at' => $purchase->due_at,
            'original_amount' => $purchase->total, 'paid_amount' => 0, 'credited_amount' => 0,
            'balance_amount' => $purchase->total, 'status' => PayableStatus::Pending,
        ]);
    }
}
