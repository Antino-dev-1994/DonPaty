<?php

namespace App\Modules\Production\Application;

use App\Models\User;
use App\Modules\Production\Domain\Enums\ProductionStatus;
use App\Modules\Production\Domain\Models\ProductionIncident;
use App\Modules\Production\Domain\Models\ProductionOrder;
use DomainException;

class RecordProductionIncident
{
    public function execute(ProductionOrder $order, string $type, string $description, User $actor, ?string $quantity = null, ?int $amount = null): ProductionIncident
    {
        if ($order->status !== ProductionStatus::InProgress) throw new DomainException('Las novedades solo se registran durante una producción en proceso.');
        return $order->incidents()->create(['incident_type' => $type, 'description' => $description, 'quantity' => $quantity, 'amount' => $amount, 'recorded_by' => $actor->id, 'recorded_at' => now()]);
    }
}
