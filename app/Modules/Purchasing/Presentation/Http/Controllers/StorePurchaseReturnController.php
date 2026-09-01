<?php

namespace App\Modules\Purchasing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Purchasing\Application\Data\PurchaseReturnData;
use App\Modules\Purchasing\Application\Data\PurchaseReturnLineData;
use App\Modules\Purchasing\Application\ReturnPurchaseItems;
use App\Modules\Purchasing\Domain\Models\Purchase;
use App\Modules\Purchasing\Presentation\Http\Requests\StorePurchaseReturnRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class StorePurchaseReturnController extends Controller
{
    public function __invoke(StorePurchaseReturnRequest $request, Purchase $purchase, ReturnPurchaseItems $action): RedirectResponse
    {
        $validated = $request->validated();
        $action->execute($purchase, new PurchaseReturnData(
            returnedAt: Carbon::parse($validated['returned_at']), reason: $validated['reason'], creator: $request->user(),
            lines: array_map(PurchaseReturnLineData::fromArray(...), $validated['lines']),
        ));

        return to_route('purchasing.purchases.show', $purchase)->with('success', 'Devolución confirmada correctamente.');
    }
}
