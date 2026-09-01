<?php

namespace App\Modules\Sales\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Sales\Domain\Enums\SaleStatus;
use App\Modules\Sales\Domain\Models\Sale;
use App\Modules\Sales\Domain\Models\SaleReturnLine;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateSaleReturnController extends Controller
{
    public function __invoke(Request $request, Sale $sale): Response
    {
        abort_unless($request->user()->hasPermission('sales.return'), 403);
        abort_if(in_array($sale->status, [SaleStatus::Draft, SaleStatus::Reversed], true), 404);

        $sale->load(['customer:id,name', 'lines.presentation.item:id,name']);
        $returnedByLine = SaleReturnLine::query()
            ->selectRaw('sale_return_lines.sale_line_id, SUM(sale_return_lines.quantity) as quantity')
            ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_lines.sale_return_id')
            ->where('sale_returns.sale_id', $sale->id)
            ->where('sale_returns.status', 'confirmed')
            ->groupBy('sale_return_lines.sale_line_id')
            ->pluck('quantity', 'sale_line_id');

        return Inertia::render('sales/returns/Create', [
            'sale' => [
                'id' => $sale->id,
                'document_number' => $sale->document_number,
                'customer' => $sale->customer?->name ?? 'Consumidor final',
                'lines' => $sale->lines->map(function ($line) use ($returnedByLine): array {
                    $returned = (string) ($returnedByLine[$line->id] ?? '0');

                    return [
                        'id' => $line->id,
                        'product' => "{$line->presentation->item->name} — {$line->presentation->name}",
                        'sold_quantity' => $line->quantity,
                        'returned_quantity' => $returned,
                        'available_quantity' => bcsub($line->quantity, $returned, 6),
                        'line_total' => $line->line_total,
                    ];
                })->filter(fn (array $line) => bccomp($line['available_quantity'], '0', 6) > 0)->values(),
            ],
            'accounts' => FinancialAccount::query()
                ->where('accepts_payments', true)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'now' => now()->format('Y-m-d\TH:i'),
        ]);
    }
}
