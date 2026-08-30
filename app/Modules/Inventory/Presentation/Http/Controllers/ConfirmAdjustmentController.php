<?php

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\Inventory\Application\ConfirmInventoryAdjustment;
use App\Modules\Inventory\Domain\Models\InventoryAdjustment;
use App\Modules\Inventory\Presentation\Http\Requests\ConfirmAdjustmentRequest;
use Illuminate\Http\RedirectResponse;

class ConfirmAdjustmentController extends Controller
{
    public function __invoke(ConfirmAdjustmentRequest $request, InventoryAdjustment $adjustment, ConfirmInventoryAdjustment $action): RedirectResponse
    {
        $authorization = $request->validated('authorization_request_id')
            ? AuthorizationRequest::query()->findOrFail($request->validated('authorization_request_id'))
            : null;
        $action->execute($adjustment, $request->user(), $authorization);

        return back()->with('success', 'Ajuste confirmado y movimiento generado.');
    }
}
