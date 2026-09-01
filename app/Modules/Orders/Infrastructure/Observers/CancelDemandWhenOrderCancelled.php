<?php

namespace App\Modules\Orders\Infrastructure\Observers;

use App\Modules\Orders\Domain\Enums\ProductionDemandStatus;
use App\Modules\Orders\Domain\Enums\SalesOrderStatus;
use App\Modules\Orders\Domain\Models\ProductionDemand;
use App\Modules\Orders\Domain\Models\SalesOrder;

class CancelDemandWhenOrderCancelled
{
    public function updated(SalesOrder $order): void
    {
        if (! $order->wasChanged('status') || $order->status !== SalesOrderStatus::Cancelled) return;
        ProductionDemand::query()->whereHas('orderLine', fn ($lines) => $lines->where('sales_order_id', $order->id))->update(['pending_quantity' => 0, 'pending_dough_quantity' => 0, 'suggested_flour_quantity' => 0, 'status' => ProductionDemandStatus::Cancelled]);
    }
}
