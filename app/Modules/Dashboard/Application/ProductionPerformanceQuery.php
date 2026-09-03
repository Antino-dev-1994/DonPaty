<?php

namespace App\Modules\Dashboard\Application;

use App\Modules\Dashboard\Application\Data\ReportDateRange;
use App\Modules\Production\Domain\Enums\ProductionStatus;
use App\Modules\Production\Domain\Models\ProductionOrder;

class ProductionPerformanceQuery
{
    /** @return array{totals:array<string, int|float>, rows:list<array<string, int|float|string|null>>} */
    public function execute(ReportDateRange $range): array
    {
        $orders = ProductionOrder::query()
            ->with(['recipeVersion.recipe:id,name', 'responsiblePerson:id,name', 'outputs'])
            ->where('status', ProductionStatus::Completed)
            ->whereBetween('completed_at', [$range->from, $range->to])
            ->orderByDesc('completed_at')->get();
        $expected = (float) $orders->sum(fn ($order) => (float) $order->expected_dough_quantity);
        $actual = (float) $orders->sum(fn ($order) => (float) $order->actual_dough_quantity);
        $waste = (float) $orders->sum(fn ($order) => (float) $order->waste_quantity);

        return [
            'totals' => [
                'productions' => $orders->count(),
                'flour_quantity' => round((float) $orders->sum(fn ($order) => (float) $order->flour_quantity), 6),
                'expected_dough' => round($expected, 6),
                'actual_dough' => round($actual, 6),
                'waste' => round($waste, 6),
                'yield_percentage' => $expected > 0 ? round($actual * 100 / $expected, 2) : 0,
                'waste_percentage' => $expected > 0 ? round($waste * 100 / $expected, 2) : 0,
                'total_cost' => (int) $orders->sum('total_cost'),
            ],
            'rows' => $orders->map(function (ProductionOrder $order): array {
                $expected = (float) $order->expected_dough_quantity;
                $actual = (float) $order->actual_dough_quantity;

                return [
                    'id' => $order->id,
                    'document' => $order->document_number,
                    'completed_at' => $order->completed_at->format('Y-m-d H:i'),
                    'recipe' => $order->recipeVersion->recipe->name.' · v'.$order->recipeVersion->version_number,
                    'responsible' => $order->responsiblePerson?->name,
                    'flour_quantity' => (float) $order->flour_quantity,
                    'expected_dough' => $expected,
                    'actual_dough' => $actual,
                    'waste' => (float) $order->waste_quantity,
                    'yield_percentage' => $expected > 0 ? round($actual * 100 / $expected, 2) : 0,
                    'total_cost' => $order->total_cost,
                    'cost_per_dough_kg' => $actual > 0 ? (int) round($order->total_cost / $actual) : 0,
                    'output_quantity' => round((float) $order->outputs->sum(fn ($output) => (float) $output->quantity), 6),
                ];
            })->all(),
        ];
    }
}
