<?php

namespace App\Modules\Purchasing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\Purchasing\Application\Data\PurchaseReceiptData;
use App\Modules\Purchasing\Application\Data\PurchaseReceiptLineData;
use App\Modules\Purchasing\Application\ReceivePurchase;
use App\Modules\Purchasing\Domain\Models\Purchase;
use App\Modules\Purchasing\Presentation\Http\Requests\StorePurchaseReceiptRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class StorePurchaseReceiptController extends Controller
{
    public function __invoke(StorePurchaseReceiptRequest $request, Purchase $purchase, ReceivePurchase $action): RedirectResponse
    {
        $validated = $request->validated();
        $authorization = isset($validated['authorization_request_id'])
            ? AuthorizationRequest::query()->findOrFail($validated['authorization_request_id']) : null;
        $action->execute($purchase, new PurchaseReceiptData(
            receivedAt: Carbon::parse($validated['received_at']), receiver: $request->user(),
            lines: array_map(PurchaseReceiptLineData::fromArray(...), $validated['lines']),
            notes: $validated['notes'] ?? null, authorization: $authorization,
        ));

        return to_route('purchasing.purchases.show', $purchase)->with('success', 'Recepción confirmada e inventario actualizado.');
    }
}
