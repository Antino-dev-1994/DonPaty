<?php

namespace App\Modules\Dashboard\Application;

use App\Modules\Inventory\Domain\Models\InventoryBalance;

class InventoryValuationQuery
{
    /** @return array{total_value:?int, low_count:int, negative_count:int, rows:list<array<string, int|float|string|bool|null>>} */
    public function execute(?string $presentationId = null, bool $includeValue = true): array
    {
        $allBalances = InventoryBalance::query()
            ->with(['presentation.item.baseUnit:id,code', 'presentation.stockUnit:id,code'])
            ->whereHas('presentation', fn ($query) => $query->where('is_active', true)->where('is_stockable', true))
            ->get();
        $baseTotals = $allBalances->groupBy(fn ($balance) => $balance->presentation->item_id)
            ->map(fn ($group) => $group->sum(fn ($balance) => (float) $balance->physical_quantity * (float) $balance->presentation->conversion_to_item_base));
        $balances = $presentationId ? $allBalances->where('presentation_id', $presentationId) : $allBalances;
        $rows = $balances->map(function (InventoryBalance $balance) use ($baseTotals, $includeValue): array {
            $physical = (float) $balance->physical_quantity;
            $available = (float) $balance->availableQuantity();
            $minimum = (float) $balance->presentation->item->minimum_stock;
            $isLow = $baseTotals[$balance->presentation->item_id] <= $minimum;

            return [
                'presentation_id' => $balance->presentation_id,
                'sku' => $balance->presentation->sku,
                'item' => $balance->presentation->item->name,
                'presentation' => $balance->presentation->name,
                'unit' => $balance->presentation->stockUnit->code,
                'physical_quantity' => $physical,
                'reserved_quantity' => (float) $balance->reserved_quantity,
                'available_quantity' => $available,
                'average_unit_cost' => $includeValue ? $balance->average_unit_cost : null,
                'value' => $includeValue ? (int) round($physical * $balance->average_unit_cost) : null,
                'is_low' => $isLow,
                'is_negative' => $available < 0,
            ];
        })->sortBy([['is_negative', 'desc'], ['is_low', 'desc'], ['item', 'asc']])->values();

        return ['total_value' => $includeValue ? (int) $rows->sum('value') : null, 'low_count' => $rows->where('is_low', true)->count(), 'negative_count' => $rows->where('is_negative', true)->count(), 'rows' => $rows->all()];
    }
}
