<?php

namespace App\Modules\Sales\Application\Data;

final readonly class SaleReturnLineData
{
    public function __construct(
        public string $saleLineId,
        public string $quantity,
        public bool $returnsToInventory,
        public ?string $conditionNotes = null,
    ) {}
}
