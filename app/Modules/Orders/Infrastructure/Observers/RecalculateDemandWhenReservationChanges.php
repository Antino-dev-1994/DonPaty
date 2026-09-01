<?php

namespace App\Modules\Orders\Infrastructure\Observers;

use App\Modules\Orders\Domain\Enums\ProductionDemandStatus;
use App\Modules\Orders\Domain\Models\ProductionDemand;
use App\Modules\Orders\Domain\Models\SalesOrderLine;

class RecalculateDemandWhenReservationChanges
{
    public function updated(SalesOrderLine $line): void
    {
        if (! $line->wasChanged('reserved_quantity')) return;
        $demand = ProductionDemand::query()->where('sales_order_line_id', $line->id)->first();
        if (! $demand || bccomp($demand->pending_quantity, '0', 6) <= 0) return;
        $pending = max(0, (float) $line->ordered_quantity - (float) $line->reserved_quantity);
        $factor = $pending / (float) $demand->pending_quantity;
        $demand->update(['pending_quantity' => $pending, 'pending_dough_quantity' => (float) $demand->pending_dough_quantity * $factor, 'suggested_flour_quantity' => (float) $demand->suggested_flour_quantity * $factor, 'status' => $pending <= 0 ? ProductionDemandStatus::Fulfilled : ProductionDemandStatus::Pending]);
    }
}
