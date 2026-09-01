<?php

namespace App\Modules\Sales\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Application\Data\SaleReturnLineData;
use App\Modules\Sales\Application\RegisterSaleReturn;
use App\Modules\Sales\Domain\Models\Sale;
use App\Modules\Sales\Presentation\Http\Requests\StoreSaleReturnRequest;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;

class StoreSaleReturnController extends Controller
{
    public function __invoke(StoreSaleReturnRequest $request, Sale $sale, RegisterSaleReturn $action): RedirectResponse
    {
        $data = $request->validated();
        $lines = array_map(
            fn (array $line) => new SaleReturnLineData(
                $line['sale_line_id'],
                (string) $line['quantity'],
                (bool) $line['returns_to_inventory'],
                $line['condition_notes'] ?? null,
            ),
            $data['lines'],
        );
        $saleReturn = $action->execute(
            $sale,
            $lines,
            CarbonImmutable::parse($data['returned_at']),
            $data['reason'],
            $request->user(),
            $data['refund_account_id'] ?? null,
        );

        return to_route('sales.show', $sale)->with('success', "Devolución {$saleReturn->document_number} registrada.");
    }
}
