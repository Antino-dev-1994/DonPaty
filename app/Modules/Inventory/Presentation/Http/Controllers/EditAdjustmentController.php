<?php

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Inventory\Domain\Enums\InventoryDocumentStatus;
use App\Modules\Inventory\Domain\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EditAdjustmentController extends Controller
{
    public function __invoke(Request $request, InventoryAdjustment $adjustment): Response
    {
        abort_unless($request->user()->hasPermission('inventory.adjust'), 403);
        abort_unless($adjustment->status === InventoryDocumentStatus::Draft, 422, 'Solo se pueden corregir borradores.');
        $adjustment->load('lines');

        return Inertia::render('inventory/adjustments/Create', [
            'defaultAdjustmentType' => 'manual',
            'presentations' => ProductPresentation::query()->with(['item:id,name,allow_negative_stock', 'stockUnit:id,code', 'inventoryBalance'])->where('is_active', true)->where('is_stockable', true)->orderBy('name')->get()->map(fn (ProductPresentation $presentation) => [
                'id' => $presentation->id, 'name' => $presentation->item->name.' · '.$presentation->name, 'sku' => $presentation->sku,
                'unit' => $presentation->stockUnit->code, 'physical_quantity' => $presentation->inventoryBalance?->physical_quantity ?? '0.000000',
                'average_unit_cost' => $presentation->inventoryBalance?->average_unit_cost ?? 0, 'allow_negative_stock' => $presentation->item->allow_negative_stock,
            ]),
            'adjustment' => [
                'id' => $adjustment->id, 'adjustment_type' => $adjustment->adjustment_type,
                'effective_at' => $adjustment->effective_at->timezone(config('regional.display_timezone'))->format('Y-m-d\\TH:i'), 'reason' => $adjustment->reason,
                'lines' => $adjustment->lines->map(fn ($line) => ['presentation_id' => $line->presentation_id, 'counted_quantity' => $line->counted_quantity, 'unit_cost' => $line->unit_cost]),
            ],
        ]);
    }
}
