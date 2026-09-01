<?php

namespace App\Modules\Purchasing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Purchasing\Domain\Models\Purchase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreatePurchaseReturnController extends Controller
{
    public function __invoke(Request $request, Purchase $purchase): Response
    {
        abort_unless($request->user()->hasPermission('purchases.manage'), 403);
        $purchase->load(['supplier:id,name', 'lines.presentation.item:id,name', 'lines.presentation.stockUnit:id,code']);

        return Inertia::render('purchasing/returns/Create', [
            'purchase' => [
                'id' => $purchase->id, 'document_number' => $purchase->document_number,
                'supplier' => $purchase->supplier->name, 'balance_amount' => $purchase->balance_amount,
                'lines' => $purchase->lines->filter(fn ($line) => bccomp($line->received_quantity, $line->returned_quantity, 6) > 0)
                    ->values()->map(fn ($line) => [
                        'id' => $line->id, 'presentation' => "{$line->presentation->item->name} — {$line->presentation->name}",
                        'unit' => $line->presentation->stockUnit->code,
                        'available_quantity' => bcsub($line->received_quantity, $line->returned_quantity, 6),
                    ]),
            ],
            'now' => now()->format('Y-m-d\TH:i'),
        ]);
    }
}
