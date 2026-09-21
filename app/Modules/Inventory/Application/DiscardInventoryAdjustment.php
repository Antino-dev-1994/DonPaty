<?php

namespace App\Modules\Inventory\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Inventory\Domain\Enums\InventoryDocumentStatus;
use App\Modules\Inventory\Domain\Models\InventoryAdjustment;
use DomainException;
use Illuminate\Support\Facades\DB;

class DiscardInventoryAdjustment
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(InventoryAdjustment $adjustment, User $actor): void
    {
        DB::transaction(function () use ($adjustment, $actor): void {
            $adjustment = InventoryAdjustment::query()->with('lines')->lockForUpdate()->findOrFail($adjustment->id);
            if ($adjustment->status !== InventoryDocumentStatus::Draft) {
                throw new DomainException('Solo se pueden descartar borradores de ajuste.');
            }

            $before = $adjustment->toArray();
            $this->audit->execute('inventory.adjustment_discarded', $adjustment, $actor, before: $before);
            $adjustment->delete();
        });
    }
}
