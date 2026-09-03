<?php

namespace App\Modules\Dashboard\Application;

use App\Modules\Dashboard\Application\Data\ReportDateRange;
use App\Modules\Finance\Domain\Enums\FinancialDocumentStatus;
use App\Modules\Sales\Domain\Models\SaleLine;
use App\Modules\Sales\Domain\Models\SaleReturnLine;

class ProductMarginQuery
{
    /** @return list<array<string, int|float|string>> */
    public function execute(ReportDateRange $range, ?string $presentationId = null): array
    {
        $rows = [];
        $lines = SaleLine::query()
            ->with('presentation.item:id,name')
            ->whereHas('sale', fn ($query) => $query->whereIn('status', ['confirmed', 'partially_paid', 'paid'])->whereBetween('sold_at', [$range->from, $range->to]))
            ->when($presentationId, fn ($query, $id) => $query->where('presentation_id', $id))
            ->get();
        foreach ($lines as $line) {
            $key = $line->presentation_id;
            $rows[$key] ??= $this->emptyRow($line->presentation->item->name, $line->presentation->name, $key);
            $rows[$key]['quantity'] += (float) $line->quantity;
            $rows[$key]['sales'] += $line->line_total;
            $rows[$key]['cost'] += $line->total_cost;
        }
        $returns = SaleReturnLine::query()
            ->with('saleLine.presentation.item:id,name')
            ->whereHas('saleReturn', fn ($query) => $query->where('status', FinancialDocumentStatus::Confirmed)->whereBetween('returned_at', [$range->from, $range->to]))
            ->when($presentationId, fn ($query, $id) => $query->whereHas('saleLine', fn ($line) => $line->where('presentation_id', $id)))
            ->get();
        foreach ($returns as $return) {
            $presentation = $return->saleLine->presentation;
            $key = $presentation->id;
            $rows[$key] ??= $this->emptyRow($presentation->item->name, $presentation->name, $key);
            $rows[$key]['quantity'] -= (float) $return->quantity;
            $rows[$key]['sales'] -= $return->refund_amount;
            if ($return->returns_to_inventory) {
                $rows[$key]['cost'] -= $return->cost_amount;
            }
        }
        foreach ($rows as &$row) {
            $row['margin'] = $row['sales'] - $row['cost'];
            $row['margin_percentage'] = $row['sales'] !== 0 ? round($row['margin'] * 100 / $row['sales'], 1) : 0;
        }

        return collect($rows)->sortByDesc('margin')->values()->all();
    }

    /** @return array<string, int|float|string> */
    private function emptyRow(string $item, string $presentation, string $id): array
    {
        return ['presentation_id' => $id, 'product' => $item, 'presentation' => $presentation, 'quantity' => 0.0, 'sales' => 0, 'cost' => 0, 'margin' => 0, 'margin_percentage' => 0.0];
    }
}
