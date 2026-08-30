<?php

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Application\CreateInventoryAdjustment;
use App\Modules\Inventory\Application\Data\InventoryAdjustmentData;
use App\Modules\Inventory\Presentation\Http\Requests\StoreAdjustmentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class StoreAdjustmentController extends Controller
{
    public function __invoke(StoreAdjustmentRequest $request, CreateInventoryAdjustment $action): RedirectResponse
    {
        $data = $request->validated();
        $adjustment = $action->execute(new InventoryAdjustmentData(
            type: $data['adjustment_type'],
            effectiveAt: Carbon::parse($data['effective_at'], config('regional.display_timezone'))->utc(),
            reason: $data['reason'],
            creator: $request->user(),
            lines: $data['lines'],
        ));

        return to_route('inventory.adjustments.show', $adjustment)->with('success', 'Ajuste guardado como borrador.');
    }
}
