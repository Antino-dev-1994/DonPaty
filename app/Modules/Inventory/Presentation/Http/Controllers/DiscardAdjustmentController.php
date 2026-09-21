<?php

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Application\DiscardInventoryAdjustment;
use App\Modules\Inventory\Domain\Models\InventoryAdjustment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DiscardAdjustmentController extends Controller
{
    public function __invoke(Request $request, InventoryAdjustment $adjustment, DiscardInventoryAdjustment $action): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('inventory.adjust'), 403);
        $action->execute($adjustment, $request->user());

        return to_route('inventory.adjustments.index')->with('success', 'Borrador descartado sin afectar el inventario.');
    }
}
