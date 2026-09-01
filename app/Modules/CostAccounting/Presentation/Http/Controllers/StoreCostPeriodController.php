<?php

namespace App\Modules\CostAccounting\Presentation\Http\Controllers;

use App\Http\Controllers\Controller; use App\Modules\CostAccounting\Application\Data\OpenCostPeriodData; use App\Modules\CostAccounting\Application\Data\UtilityCostData; use App\Modules\CostAccounting\Application\OpenCostPeriod; use App\Modules\CostAccounting\Presentation\Http\Requests\OpenCostPeriodRequest; use Illuminate\Http\RedirectResponse;
class StoreCostPeriodController extends Controller
{
    public function __invoke(OpenCostPeriodRequest $request,OpenCostPeriod $action):RedirectResponse
    {
        $data=$request->validated(); $period=$action->execute(new OpenCostPeriodData((int)$data['year'],(int)$data['month'],$request->user(),array_map(UtilityCostData::fromArray(...),$data['utilities'])));
        return to_route('cost-periods.show',$period)->with('success','Periodo de costos abierto correctamente.');
    }
}
