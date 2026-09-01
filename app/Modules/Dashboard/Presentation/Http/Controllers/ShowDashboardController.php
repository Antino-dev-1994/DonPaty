<?php

namespace App\Modules\Dashboard\Presentation\Http\Controllers;

use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use App\Modules\Orders\Domain\Models\SalesOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShowDashboardController
{
    public function __invoke(Request $request): Response
    {
        $currentPeriodExists = CostPeriod::query()->where('year', now()->year)->where('month', now()->month)->exists();

        return Inertia::render('dashboard/Index', [
            'costPeriodAlert' => $request->user()->hasPermission('cost-periods.view') && ! $currentPeriodExists
                ? ['message' => 'Falta abrir el periodo de costos de este mes. Debes registrar gas y electricidad antes de completar producciones.', 'canManage' => $request->user()->hasPermission('cost-periods.manage')]
                : null,
            'upcomingOrders' => $request->user()->hasPermission('orders.view')
                ? SalesOrder::query()->with('customer:id,name')->where('due_at', '<=', now()->addHours(24))->whereNotIn('status', ['delivered', 'cancelled'])->orderBy('due_at')->limit(10)->get()->map(fn ($order) => ['id' => $order->id, 'document_number' => $order->document_number, 'customer' => $order->customer->name, 'due_at' => $order->due_at->format('Y-m-d H:i'), 'is_overdue' => $order->due_at->isPast(), 'status' => $order->status->label()])
                : [],
        ]);
    }
}
