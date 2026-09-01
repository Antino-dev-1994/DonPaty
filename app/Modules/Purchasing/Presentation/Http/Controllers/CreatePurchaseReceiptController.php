<?php

namespace App\Modules\Purchasing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Purchasing\Domain\Models\Purchase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreatePurchaseReceiptController extends Controller
{
    public function __invoke(Request $request, Purchase $purchase): Response
    {
        abort_unless($request->user()->hasPermission('purchases.receive'), 403);
        $purchase->load(['supplier:id,name', 'lines.presentation.item:id,name', 'lines.presentation.stockUnit:id,code']);

        return Inertia::render('purchasing/receipts/Create', [
            'purchase' => [
                'id' => $purchase->id, 'document_number' => $purchase->document_number, 'supplier' => $purchase->supplier->name,
                'lines' => $purchase->lines->filter(fn ($line) => bccomp($line->received_quantity, $line->ordered_quantity, 6) < 0)
                    ->values()->map(fn ($line) => [
                        'id' => $line->id, 'presentation' => "{$line->presentation->item->name} — {$line->presentation->name}",
                        'unit' => $line->presentation->stockUnit->code, 'ordered_quantity' => $line->ordered_quantity,
                        'received_quantity' => $line->received_quantity,
                        'remaining_quantity' => bcsub($line->ordered_quantity, $line->received_quantity, 6),
                    ]),
            ],
            'now' => now()->format('Y-m-d\TH:i'),
        ]);
    }
}
