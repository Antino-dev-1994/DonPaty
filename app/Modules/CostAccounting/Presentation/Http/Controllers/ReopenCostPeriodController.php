<?php

namespace App\Modules\CostAccounting\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CostAccounting\Application\ReopenCostPeriod;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use App\Modules\CostAccounting\Presentation\Http\Requests\ReopenCostPeriodRequest;
use Illuminate\Http\RedirectResponse;

class ReopenCostPeriodController extends Controller
{
    public function __invoke(ReopenCostPeriodRequest $request, CostPeriod $costPeriod, ReopenCostPeriod $action): RedirectResponse
    {
        $action->execute($costPeriod, $request->validated('reason'), $request->user());

        return back()->with('success', 'Periodo reabierto y conciliación revertida.');
    }
}
