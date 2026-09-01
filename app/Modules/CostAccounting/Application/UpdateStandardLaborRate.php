<?php

namespace App\Modules\CostAccounting\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\CostAccounting\Domain\Enums\CostPeriodStatus;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use DomainException;

class UpdateStandardLaborRate
{
    public function __construct(private readonly RecordAuditEvent $audit) {}
    public function execute(CostPeriod $period, int $rate, string $reason, User $actor): CostPeriod
    {
        if ($period->status !== CostPeriodStatus::Open || $rate < 0 || trim($reason) === '') throw new DomainException('La tarifa de un periodo abierto requiere valor válido y motivo.');
        $before = $period->only(['standard_labor_rate_per_kg', 'labor_rate_reason']);
        $period->update(['standard_labor_rate_per_kg' => $rate, 'labor_rate_reason' => $reason]);
        $this->audit->execute('costs.labor_rate_updated', $period, $actor, before: $before, after: $period->only(['standard_labor_rate_per_kg', 'labor_rate_reason']));
        return $period;
    }
}
