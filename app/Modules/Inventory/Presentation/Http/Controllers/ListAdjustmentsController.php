<?php

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListAdjustmentsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('inventory.view'), 403);

        return Inertia::render('inventory/adjustments/Index', [
            'adjustments' => InventoryAdjustment::query()->with('creator:id,name')->withCount('lines')->latest()->paginate(25)->through(fn (InventoryAdjustment $adjustment) => [
                'id' => $adjustment->id,
                'document_number' => $adjustment->document_number,
                'type' => $adjustment->adjustment_type === 'initial' ? 'Inventario inicial' : 'Ajuste manual',
                'status' => $adjustment->status->label(),
                'status_value' => $adjustment->status->value,
                'effective_at' => $adjustment->effective_at->timezone(config('regional.display_timezone'))->format('Y-m-d H:i'),
                'reason' => $adjustment->reason,
                'creator' => $adjustment->creator->name,
                'lines_count' => $adjustment->lines_count,
            ]),
            'canAdjust' => $request->user()->hasPermission('inventory.adjust'),
        ]);
    }
}
