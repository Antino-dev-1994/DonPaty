<?php

namespace App\Modules\CostAccounting\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EditLaborRateController extends Controller
{
    public function __invoke(Request $request, CostPeriod $costPeriod): Response
    {
        abort_unless($request->user()->hasPermission('cost-periods.manage'), 403);
        return Inertia::render('costs/periods/LaborRate', ['period' => [...$costPeriod->only(['id', 'standard_labor_rate_per_kg', 'labor_rate_reason']), 'label' => $costPeriod->label()]]);
    }
}
