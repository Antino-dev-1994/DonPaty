<?php

namespace App\Modules\Pricing\Application\Data;

use App\Modules\Pricing\Domain\Enums\PriceListType;
use Carbon\CarbonInterface;

final readonly class PriceListData
{
    /** @param list<array{presentation_id:string,price:int,minimum_price:?int}> $items */
    public function __construct(public string $name, public PriceListType $type, public ?CarbonInterface $startsAt, public ?CarbonInterface $endsAt, public bool $isDefault, public bool $isActive, public array $items) {}
}
