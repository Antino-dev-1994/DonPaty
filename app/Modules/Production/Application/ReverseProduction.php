<?php

namespace App\Modules\Production\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\CostAccounting\Domain\Models\CostAllocation;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\Inventory\Application\ReverseInventoryMovement;
use App\Modules\Production\Domain\Enums\ProductionStatus;
use App\Modules\Production\Domain\Models\ProductionOrder;
use DomainException;
use Illuminate\Support\Facades\DB;

class ReverseProduction
{
    public function __construct(
        private readonly ReverseInventoryMovement $reverseMovement,
        private readonly ProductionAuthorizationGuard $authorizationGuard,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(ProductionOrder $order, User $actor, string $reason, ?AuthorizationRequest $authorization = null): ProductionOrder
    {
        if (trim($reason) === '') throw new DomainException('La reversión requiere un motivo.');
        return DB::transaction(function () use ($order, $actor, $reason, $authorization): ProductionOrder {
            $order = ProductionOrder::query()->with(['outputMovement', 'consumptionMovement', 'costPeriod'])->lockForUpdate()->findOrFail($order->id);
            if ($order->status !== ProductionStatus::Completed) throw new DomainException('Solo se puede revertir una producción completada.');
            if ($authorization) $this->authorizationGuard->ensureUsableFor($authorization, $order, 'inventory.authorize-negative');
            $this->reverseMovement->execute($order->outputMovement, $actor, "Reversión de productos: {$reason}", $authorization);
            $this->reverseMovement->execute($order->consumptionMovement, $actor, "Reintegro de ingredientes: {$reason}");
            $newProcessed = max(0, (float) $order->costPeriod->processed_flour_quantity - (float) $order->flour_quantity);
            $order->costPeriod->update(['processed_flour_quantity' => $newProcessed]);
            CostAllocation::query()->where('production_order_id', $order->id)->update(['reversed_at' => now()]);
            $order->update(['status' => ProductionStatus::Reversed, 'reversed_by' => $actor->id, 'reversed_at' => now(), 'reversal_reason' => $reason]);
            $this->audit->execute('production.order_reversed', $order, $actor, after: ['reason' => $reason], authorizationRequestId: $authorization?->id);
            return $order->fresh();
        });
    }
}
