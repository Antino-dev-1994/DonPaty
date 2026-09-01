<?php

namespace App\Modules\Purchasing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Purchasing\Application\CreatePurchase;
use App\Modules\Purchasing\Application\Data\PurchaseData;
use App\Modules\Purchasing\Application\Data\PurchaseLineData;
use App\Modules\Purchasing\Domain\Enums\PurchasePaymentCondition;
use App\Modules\Purchasing\Presentation\Http\Requests\StorePurchaseRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class StorePurchaseController extends Controller
{
    public function __invoke(StorePurchaseRequest $request, CreatePurchase $action): RedirectResponse
    {
        $validated = $request->validated();
        $purchase = $action->execute(new PurchaseData(
            supplierPersonId: $validated['supplier_person_id'],
            supplierDocumentNumber: $validated['supplier_document_number'] ?? null,
            issuedAt: Carbon::parse($validated['issued_at']),
            dueAt: isset($validated['due_at']) ? Carbon::parse($validated['due_at']) : null,
            paymentCondition: PurchasePaymentCondition::from($validated['payment_condition']),
            additionalCosts: (int) $validated['additional_costs'], notes: $validated['notes'] ?? null,
            creator: $request->user(), lines: array_map(PurchaseLineData::fromArray(...), $validated['lines']),
        ));

        return to_route('purchasing.purchases.show', $purchase)->with('success', 'Compra registrada correctamente.');
    }
}
