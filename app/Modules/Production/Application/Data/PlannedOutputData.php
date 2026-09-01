<?php

namespace App\Modules\Production\Application\Data;

final readonly class PlannedOutputData
{
    public function __construct(public string $compatibleProductId, public string $quantity) {}
    /** @param array<string,mixed> $data */
    public static function fromArray(array $data): self { return new self($data['compatible_product_id'], (string) $data['quantity']); }
}
