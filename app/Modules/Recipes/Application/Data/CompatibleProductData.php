<?php

namespace App\Modules\Recipes\Application\Data;

final readonly class CompatibleProductData
{
    /** @param list<FinishingComponentData> $finishingComponents */
    public function __construct(public string $presentationId, public string $doughWeightPerUnit, public string $doughWeightUnitId, public string $bakingLossPercentage, public string $costWeightFactor, public array $finishingComponents) {}
    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self($data['presentation_id'], (string) $data['dough_weight_per_unit'], $data['dough_weight_unit_id'], (string) $data['baking_loss_percentage'], (string) $data['cost_weight_factor'], array_map(FinishingComponentData::fromArray(...), $data['finishing_components'] ?? []));
    }
}
