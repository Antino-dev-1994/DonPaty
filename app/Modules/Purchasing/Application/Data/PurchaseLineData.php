<?php

namespace App\Modules\Purchasing\Application\Data;

final readonly class PurchaseLineData
{
    public function __construct(
        public string $presentationId,
        public string $quantity,
        public int $unitPrice,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self($data['presentation_id'], (string) $data['quantity'], (int) $data['unit_price']);
    }

    public function total(): int
    {
        return (int) round((float) $this->quantity * $this->unitPrice);
    }
}
