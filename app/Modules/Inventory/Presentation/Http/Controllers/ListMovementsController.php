<?php

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\InventoryMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListMovementsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('inventory.view'), 403);

        return Inertia::render('inventory/movements/Index', [
            'movements' => InventoryMovement::query()
                ->with(['creator:id,name', 'lines.presentation:id,sku,name'])
                ->latest('effective_at')
                ->paginate(30)
                ->through(fn (InventoryMovement $movement) => [
                    'id' => $movement->id,
                    'document_number' => $movement->document_number,
                    'movement_type' => $movement->movement_type->label(),
                    'status' => $movement->status->label(),
                    'effective_at' => $movement->effective_at->timezone(config('regional.display_timezone'))->format('Y-m-d H:i'),
                    'creator' => $movement->creator->name,
                    'notes' => $movement->notes,
                    'lines' => $movement->lines->map(fn ($line) => [
                        'presentation' => $line->presentation->name,
                        'sku' => $line->presentation->sku,
                        'quantity_in' => $line->quantity_in,
                        'quantity_out' => $line->quantity_out,
                        'unit_cost' => $line->unit_cost,
                        'balance_after' => $line->balance_after,
                    ]),
                ]),
        ]);
    }
}
