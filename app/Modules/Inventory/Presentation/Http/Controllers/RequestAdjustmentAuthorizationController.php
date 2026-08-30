<?php

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Application\RequestAdjustmentAuthorization;
use App\Modules\Inventory\Domain\Models\InventoryAdjustment;
use App\Modules\Inventory\Presentation\Http\Requests\RequestNegativeAuthorizationRequest;
use Illuminate\Http\RedirectResponse;

class RequestAdjustmentAuthorizationController extends Controller
{
    public function __invoke(RequestNegativeAuthorizationRequest $request, InventoryAdjustment $adjustment, RequestAdjustmentAuthorization $action): RedirectResponse
    {
        $action->execute($adjustment, $request->user(), $request->validated('reason'));

        return back()->with('success', 'Autorización solicitada. Debe aprobarla otro usuario autorizado.');
    }
}
