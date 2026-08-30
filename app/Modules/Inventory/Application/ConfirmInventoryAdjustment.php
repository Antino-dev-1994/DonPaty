<?php

namespace App\Modules\Inventory\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\Inventory\Application\Data\InventoryMovementData;
use App\Modules\Inventory\Application\Data\InventoryMovementLineData;
use App\Modules\Inventory\Domain\Enums\InventoryDocumentStatus;
use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use App\Modules\Inventory\Domain\Models\InventoryAdjustment;
use DomainException;
use Illuminate\Support\Facades\DB;

class ConfirmInventoryAdjustment
{
    public function __construct(
        private readonly PostInventoryMovement $postMovement,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(InventoryAdjustment $adjustment, User $actor, ?AuthorizationRequest $authorization = null): void
    {
        DB::transaction(function () use ($adjustment, $actor, $authorization): void {
            $adjustment = InventoryAdjustment::query()->with('lines')->lockForUpdate()->findOrFail($adjustment->id);
            if ($adjustment->status !== InventoryDocumentStatus::Draft) {
                throw new DomainException('El ajuste ya fue confirmado.');
            }

            if ($authorization && ($authorization->resource_type !== $adjustment->getMorphClass() || $authorization->resource_id !== $adjustment->id)) {
                throw new DomainException('La autorización no corresponde a este ajuste.');
            }

            $lines = $adjustment->lines
                ->filter(fn ($line) => bccomp($line->difference_quantity, '0', 6) !== 0)
                ->map(function ($line): InventoryMovementLineData {
                    if (bccomp($line->difference_quantity, '0', 6) > 0) {
                        return InventoryMovementLineData::incoming(
                            $line->presentation_id,
                            $line->difference_quantity,
                            $line->unit_cost,
                        );
                    }

                    return InventoryMovementLineData::outgoing(
                        $line->presentation_id,
                        ltrim($line->difference_quantity, '-'),
                    );
                })->values()->all();

            if ($lines === []) {
                throw new DomainException('El ajuste no contiene diferencias para confirmar.');
            }

            $movement = $this->postMovement->execute(new InventoryMovementData(
                type: $adjustment->adjustment_type === 'initial' ? InventoryMovementType::InitialBalance : InventoryMovementType::ManualAdjustment,
                effectiveAt: $adjustment->effective_at,
                creator: $actor,
                lines: $lines,
                source: $adjustment,
                notes: $adjustment->reason,
                authorization: $authorization,
            ));

            $adjustment->update([
                'status' => InventoryDocumentStatus::Confirmed,
                'confirmed_by' => $actor->id,
                'inventory_movement_id' => $movement->id,
            ]);
            $this->audit->execute('inventory.adjustment_confirmed', $adjustment, $actor, after: $adjustment->fresh()->toArray(), authorizationRequestId: $authorization?->id);
        });
    }
}
