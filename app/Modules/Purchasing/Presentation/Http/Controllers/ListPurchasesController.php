<?php

namespace App\Modules\Purchasing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Purchasing\Domain\Models\Purchase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListPurchasesController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('purchases.manage') || $request->user()->hasPermission('purchases.receive'), 403);
        $search = trim($request->string('search')->toString());

        return Inertia::render('purchasing/purchases/Index', [
            'purchases' => Purchase::query()->with('supplier:id,name')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('document_number', 'like', "%{$search}%")
                        ->orWhere('supplier_document_number', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                }))
                ->latest('issued_at')->latest('created_at')->paginate(25)->withQueryString()
                ->through(fn (Purchase $purchase) => [
                    ...$purchase->only(['id', 'document_number', 'supplier_document_number', 'subtotal', 'additional_costs', 'total', 'balance_amount']),
                    'issued_at' => $purchase->issued_at->toDateString(), 'supplier' => $purchase->supplier->name,
                    'receipt_status' => $purchase->receipt_status->value, 'receipt_status_label' => $purchase->receipt_status->label(),
                    'payment_status' => $purchase->payment_status->value, 'payment_status_label' => $purchase->payment_status->label(),
                ]),
            'filters' => ['search' => $search],
            'canCreate' => $request->user()->hasPermission('purchases.manage'),
        ]);
    }
}
