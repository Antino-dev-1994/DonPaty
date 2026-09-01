<?php

namespace App\Modules\Purchasing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Purchasing\Domain\Models\Purchase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowPurchaseController extends Controller
{
    public function __invoke(Request $request, Purchase $purchase): Response
    {
        abort_unless($request->user()->hasPermission('purchases.manage') || $request->user()->hasPermission('purchases.receive'), 403);
        $purchase->load(['supplier:id,name,document_number', 'lines.presentation.item:id,name', 'lines.presentation.stockUnit:id,code', 'receipts:id,purchase_id,document_number,received_at,status']);

        return Inertia::render('purchasing/purchases/Show', [
            'purchase' => [
                ...$purchase->only(['id', 'document_number', 'supplier_document_number', 'subtotal', 'additional_costs', 'total', 'paid_amount', 'balance_amount', 'notes']),
                'issued_at' => $purchase->issued_at->toDateString(), 'due_at' => $purchase->due_at?->toDateString(),
                'supplier' => $purchase->supplier->only(['name', 'document_number']),
                'payment_condition' => $purchase->payment_condition->label(),
                'receipt_status' => $purchase->receipt_status->label(), 'payment_status' => $purchase->payment_status->label(),
                'lines' => $purchase->lines->map(fn ($line) => [
                    ...$line->only(['id', 'ordered_quantity', 'received_quantity', 'returned_quantity', 'unit_price', 'allocated_additional_cost', 'line_total']),
                    'presentation' => "{$line->presentation->item->name} — {$line->presentation->name}",
                    'unit' => $line->presentation->stockUnit->code,
                ]),
                'receipts' => $purchase->receipts->map(fn ($receipt) => [
                    'id' => $receipt->id, 'document_number' => $receipt->document_number,
                    'received_at' => $receipt->received_at->format('Y-m-d H:i'),
                ]),
            ],
            'canReceive' => $request->user()->hasPermission('purchases.receive'),
            'canPay' => $request->user()->hasPermission('payables.manage'),
        ]);
    }
}
