<?php

namespace App\Modules\Production\Application;

use App\Modules\Catalog\Domain\Services\UnitConverter;
use App\Modules\Inventory\Domain\Models\InventoryBalance;
use App\Modules\Production\Domain\Models\ProductionOrder;

class ProductionAvailability
{
    public function __construct(private readonly UnitConverter $converter) {}

    /** @return list<array<string,mixed>> */
    public function execute(ProductionOrder $order): array
    {
        $order->loadMissing(['consumptions.item', 'consumptions.unit', 'consumptions.presentation.stockUnit']);
        return $order->consumptions->map(function ($line): array {
            $required = $this->converter->convert($line->calculated_quantity, $line->unit, $line->presentation->stockUnit);
            $balance = InventoryBalance::query()->where('presentation_id', $line->presentation_id)->first();
            $available = $balance ? bcsub($balance->physical_quantity, $balance->reserved_quantity, 6) : '0';
            return ['consumption_id' => $line->id, 'item' => $line->item->name, 'presentation' => $line->presentation->name, 'required_quantity' => $required, 'available_quantity' => $available, 'unit' => $line->presentation->stockUnit->code, 'is_short' => bccomp($available, $required, 6) < 0];
        })->all();
    }
}
