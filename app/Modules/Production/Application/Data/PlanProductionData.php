<?php

namespace App\Modules\Production\Application\Data;

use App\Models\User;
use App\Modules\Production\Domain\Enums\LaborMethod;
use Carbon\CarbonInterface;

final readonly class PlanProductionData
{
    /** @param list<PlannedOutputData> $outputs */
    public function __construct(public string $recipeVersionId, public CarbonInterface $plannedFor, public string $flourQuantityKg, public LaborMethod $laborMethod, public string $responsiblePersonId, public User $creator, public array $outputs) {}
}
