<?php

namespace App\Modules\Finance\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\Payable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateSupplierPaymentController extends Controller
{
    public function __invoke(Request $request, Payable $payable): Response
    {
        abort_unless($request->user()->hasPermission('payables.manage'), 403);
        abort_unless($payable->balance_amount > 0, 404);
        $payable->load('person:id,name');

        return Inertia::render('finance/payables/Pay', [
            'payable' => [
                'id' => $payable->id, 'document_number' => $payable->document_number,
                'person' => $payable->person->name, 'original_amount' => $payable->original_amount,
                'paid_amount' => $payable->paid_amount, 'balance_amount' => $payable->balance_amount,
                'back_url' => $payable->source instanceof \App\Modules\Purchasing\Domain\Models\Purchase
                    ? route('purchasing.purchases.show', $payable->source_id, false)
                    : route('finance.index', absolute: false),
            ],
            'accounts' => FinancialAccount::query()->where('accepts_payments', true)->where('is_active', true)
                ->orderBy('name')->get(['id', 'code', 'name']),
            'now' => now()->format('Y-m-d\TH:i'),
        ]);
    }
}
