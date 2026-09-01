<?php

namespace App\Modules\Purchasing\Application\Data;

final readonly class PurchaseReturnLineData
{
    public function __construct(public string $purchaseLineId, public string $quantity) {}
    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self { return new self($data['purchase_line_id'], (string) $data['quantity']); }
}
