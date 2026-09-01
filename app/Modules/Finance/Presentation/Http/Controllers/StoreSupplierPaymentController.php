<?php

namespace App\Modules\Finance\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Application\Data\SupplierPaymentData;
use App\Modules\Finance\Application\PaySupplierPayable;
use App\Modules\Finance\Domain\Models\Payable;
use App\Modules\Finance\Presentation\Http\Requests\StoreSupplierPaymentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class StoreSupplierPaymentController extends Controller
{
    public function __invoke(StoreSupplierPaymentRequest $request, Payable $payable, PaySupplierPayable $action): RedirectResponse
    {
        $validated = $request->validated();
        $isPurchase = $payable->source instanceof \App\Modules\Purchasing\Domain\Models\Purchase;
        $action->execute($payable, new SupplierPaymentData(
            amount: (int) $validated['amount'], financialAccountId: $validated['financial_account_id'],
            paidAt: Carbon::parse($validated['paid_at']), creator: $request->user(), reference: $validated['reference'] ?? null,
        ));

        return $isPurchase
            ? to_route('purchasing.purchases.show', $payable->source_id)->with('success', 'Pago aplicado correctamente.')
            : to_route('finance.index')->with('success', 'Pago aplicado correctamente.');
    }
}
