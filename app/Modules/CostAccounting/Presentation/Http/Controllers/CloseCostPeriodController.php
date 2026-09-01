<?php

namespace App\Modules\CostAccounting\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CostAccounting\Application\CloseCostPeriod;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CloseCostPeriodController extends Controller
{
    public function __invoke(Request $request, CostPeriod $costPeriod, CloseCostPeriod $action): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('cost-periods.close'), 403);
        $action->execute($costPeriod, $request->user());

        return back()->with('success', 'Periodo cerrado y costos conciliados.');
    }
}
