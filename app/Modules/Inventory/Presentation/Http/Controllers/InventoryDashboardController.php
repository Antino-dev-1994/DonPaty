<?php

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Inventory\Domain\Models\InventoryBalance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('inventory.view'), 403);
        $search = trim((string) $request->string('search'));

        $presentations = ProductPresentation::query()
            ->with(['item:id,code,name,minimum_stock', 'item.presentations.inventoryBalance', 'stockUnit:id,code', 'packageComponents'])
            ->with('inventoryBalance')
            ->where('is_stockable', true)
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%")
                    ->orWhereHas('item', fn ($query) => $query->where('name', 'like', "%{$search}%"));
            }))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString()
            ->through(function (ProductPresentation $presentation): array {
                $balance = $presentation->inventoryBalance;
                $physical = $balance?->physical_quantity ?? '0.000000';
                $reserved = $balance?->reserved_quantity ?? '0.000000';

                return [
                    'id' => $presentation->id,
                    'sku' => $presentation->sku,
                    'name' => $presentation->name,
                    'item' => $presentation->item->name,
                    'unit' => $presentation->stockUnit->code,
                    'physical_quantity' => $physical,
                    'reserved_quantity' => $reserved,
                    'available_quantity' => bcsub($physical, $reserved, 6),
                    'average_unit_cost' => $balance?->average_unit_cost ?? 0,
                    'inventory_value' => (int) round((float) $physical * ($balance?->average_unit_cost ?? 0)),
                    'is_below_minimum' => bccomp(
                        (string) $presentation->item->presentations->sum(fn ($itemPresentation) => (float) ($itemPresentation->inventoryBalance?->physical_quantity ?? 0) * (float) $itemPresentation->conversion_to_item_base),
                        $presentation->item->minimum_stock,
                        6,
                    ) < 0,
                    'is_package' => $presentation->packageComponents->isNotEmpty(),
                ];
            });

        return Inertia::render('inventory/Index', [
            'presentations' => $presentations,
            'filters' => ['search' => $search],
            'canAdjust' => $request->user()->hasPermission('inventory.adjust'),
            'canConvertPackages' => $request->user()->hasPermission('packages.convert'),
        ]);
    }
}
