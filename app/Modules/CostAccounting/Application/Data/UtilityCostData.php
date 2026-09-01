<?php

namespace App\Modules\CostAccounting\Application\Data;

use App\Modules\CostAccounting\Domain\Enums\UtilityType;
use Carbon\CarbonInterface;

final readonly class UtilityCostData
{
    public function __construct(public UtilityType $type, public CarbonInterface $billedFrom, public CarbonInterface $billedTo, public CarbonInterface $paidAt, public int $totalAmount, public string $businessPercentage, public string $householdPercentage, public ?string $physicalConsumption, public ?string $reference, public ?int $manualRate, public ?string $overrideReason) {}
    /** @param array<string,mixed> $data */
    public static function fromArray(array $data):self
    {
        return new self(UtilityType::from($data['utility_type']),\Illuminate\Support\Carbon::parse($data['billed_from']),\Illuminate\Support\Carbon::parse($data['billed_to']),\Illuminate\Support\Carbon::parse($data['paid_at']),(int)$data['total_amount'],(string)$data['business_percentage'],(string)$data['household_percentage'],isset($data['physical_consumption'])?(string)$data['physical_consumption']:null,$data['reference']??null,isset($data['manual_rate'])&&$data['manual_rate']!==null?(int)$data['manual_rate']:null,$data['override_reason']??null);
    }
}
