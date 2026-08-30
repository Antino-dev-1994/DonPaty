<?php

namespace App\Modules\Inventory\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\Inventory\Application\Data\InventoryMovementData;
use App\Modules\Inventory\Application\Data\InventoryMovementLineData;
use App\Modules\Inventory\Domain\Enums\InventoryDocumentStatus;
use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use App\Modules\Inventory\Domain\Models\InventoryMovement;
use DomainException;
use Illuminate\Support\Facades\DB;

class ReverseInventoryMovement
{
    public function __construct(
        private readonly PostInventoryMovement $postMovement,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(InventoryMovement $movement, User $actor, string $reason, ?AuthorizationRequest $authorization = null): InventoryMovement
    {
        return DB::transaction(function () use ($movement, $actor, $reason, $authorization): InventoryMovement {
            $movement = InventoryMovement::query()->with('lines')->lockForUpdate()->findOrFail($movement->id);
            if ($movement->status !== InventoryDocumentStatus::Confirmed) {
                throw new DomainException('Solo se puede revertir un movimiento confirmado.');
            }

            $lines = $movement->lines->map(fn ($line) => bccomp($line->quantity_in, '0', 6) > 0
                ? InventoryMovementLineData::outgoing($line->presentation_id, $line->quantity_in, $line->total_cost)
                : InventoryMovementLineData::incoming($line->presentation_id, $line->quantity_out, $line->unit_cost, $line->total_cost)
            )->all();

            $reversal = $this->postMovement->execute(new InventoryMovementData(
                type: InventoryMovementType::Reversal,
                effectiveAt: now(),
                creator: $actor,
                lines: $lines,
                source: $movement,
                notes: $reason,
                authorization: $authorization,
                reversalOfId: $movement->id,
            ));
            $movement->update(['status' => InventoryDocumentStatus::Reversed]);
            $this->audit->execute('inventory.movement_reversed', $movement, $actor, after: ['reversal_id' => $reversal->id, 'reason' => $reason]);

            return $reversal;
        });
    }
}
