<?php

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Inventory\Domain\Models\InventoryMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateAdjustmentController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('inventory.adjust'), 403);

        return Inertia::render('inventory/adjustments/Create', [
            'defaultAdjustmentType' => InventoryMovement::query()->exists() ? 'manual' : 'initial',
            'presentations' => ProductPresentation::query()
                ->with(['item:id,name,allow_negative_stock', 'stockUnit:id,code', 'inventoryBalance'])
                ->where('is_active', true)->where('is_stockable', true)->orderBy('name')->get()->map(fn (ProductPresentation $presentation) => [
                    'id' => $presentation->id,
                    'name' => $presentation->item->name.' · '.$presentation->name,
                    'sku' => $presentation->sku,
                    'unit' => $presentation->stockUnit->code,
                    'physical_quantity' => $presentation->inventoryBalance?->physical_quantity ?? '0.000000',
                    'average_unit_cost' => $presentation->inventoryBalance?->average_unit_cost ?? 0,
                    'allow_negative_stock' => $presentation->item->allow_negative_stock,
                ]),
        ]);
    }
}
