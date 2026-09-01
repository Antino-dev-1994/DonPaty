<?php

namespace App\Modules\CostAccounting\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CostAccounting\Application\Data\OpenCostPeriodData;
use App\Modules\CostAccounting\Application\Data\UtilityCostData;
use App\Modules\CostAccounting\Application\OpenCostPeriod;
use App\Modules\CostAccounting\Presentation\Http\Requests\OpenCostPeriodRequest;
use Illuminate\Http\RedirectResponse;

class StoreCostPeriodController extends Controller
{
    public function __invoke(OpenCostPeriodRequest $request, OpenCostPeriod $action): RedirectResponse
    {
        $data = $request->validated();
        $period = $action->execute(new OpenCostPeriodData(
            year: (int) $data['year'],
            month: (int) $data['month'],
            opener: $request->user(),
            utilities: array_map(UtilityCostData::fromArray(...), $data['utilities']),
            standardLaborRatePerKg: (int) ($data['standard_labor_rate_per_kg'] ?? 0),
            laborRateReason: $data['labor_rate_reason'] ?? null,
        ));

        return to_route('cost-periods.show', $period)->with('success', 'Periodo de costos abierto correctamente.');
    }
}
