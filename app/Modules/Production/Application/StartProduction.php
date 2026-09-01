<?php

namespace App\Modules\Production\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\Production\Domain\Enums\ProductionStatus;
use App\Modules\Production\Domain\Models\ProductionOrder;
use DomainException;
use Illuminate\Support\Facades\DB;

class StartProduction
{
    public function __construct(
        private readonly ProductionAvailability $availability,
        private readonly ProductionAuthorizationGuard $authorizationGuard,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(ProductionOrder $order, User $actor, ?AuthorizationRequest $authorization = null): ProductionOrder
    {
        return DB::transaction(function () use ($order, $actor, $authorization): ProductionOrder {
            $order = ProductionOrder::query()->lockForUpdate()->findOrFail($order->id);
            if ($order->status !== ProductionStatus::Planned) throw new DomainException('Solo puede iniciarse una producción planificada.');
            $hasShortage = collect($this->availability->execute($order))->contains('is_short', true);
            if ($hasShortage) {
                try {
                    $this->authorizationGuard->ensureUsableFor($authorization, $order, 'inventory.authorize-negative');
                } catch (DomainException) {
                    throw new DomainException('Hay ingredientes insuficientes; se requiere una autorización vigente para esta producción.');
                }
            }
            $order->update(['status' => ProductionStatus::InProgress, 'started_at' => now(), 'negative_stock_authorization_id' => $hasShortage ? $authorization->id : null]);
            $this->audit->execute('production.order_started', $order, $actor, after: ['shortage_authorized' => $hasShortage], authorizationRequestId: $authorization?->id);
            return $order->fresh();
        });
    }
}
