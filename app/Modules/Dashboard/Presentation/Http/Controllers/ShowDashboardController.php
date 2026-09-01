<?php

namespace App\Modules\Dashboard\Presentation\Http\Controllers;

use App\Modules\CostAccounting\Domain\Models\CostPeriod;
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
        ]);
    }
}
