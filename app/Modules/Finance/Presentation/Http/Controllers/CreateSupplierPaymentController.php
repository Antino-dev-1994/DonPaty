<?php

namespace App\Modules\Finance\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\Payable;
use App\Modules\Purchasing\Domain\Models\Purchase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateSupplierPaymentController extends Controller
{
    public function __invoke(Request $request, Payable $payable): Response
    {
        abort_unless($request->user()->hasPermission('payables.manage'), 403);
        abort_unless($payable->source instanceof Purchase && $payable->balance_amount > 0, 404);
        $payable->load('person:id,name');

        return Inertia::render('finance/payables/Pay', [
            'payable' => [
                'id' => $payable->id, 'document_number' => $payable->document_number,
                'supplier' => $payable->person->name, 'original_amount' => $payable->original_amount,
                'paid_amount' => $payable->paid_amount, 'balance_amount' => $payable->balance_amount,
                'purchase_id' => $payable->source_id,
            ],
            'accounts' => FinancialAccount::query()->where('accepts_payments', true)->where('is_active', true)
                ->orderBy('name')->get(['id', 'code', 'name']),
            'now' => now()->format('Y-m-d\TH:i'),
        ]);
    }
}
