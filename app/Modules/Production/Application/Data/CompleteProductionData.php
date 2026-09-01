<?php

namespace App\Modules\Production\Application\Data;

use App\Models\User;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use Carbon\CarbonInterface;

final readonly class CompleteProductionData
{
    /** @param list<ActualConsumptionData> $consumptions @param list<ActualOutputData> $outputs @param list<LaborHoursData> $laborHours */
    public function __construct(public CarbonInterface $completedAt, public string $actualDoughQuantityKg, public string $wasteQuantityKg, public User $actor, public array $consumptions, public array $outputs, public array $laborHours = [], public ?int $manualLaborAmount = null, public ?string $manualLaborReason = null, public ?AuthorizationRequest $manualLaborAuthorization = null) {}
}
