<?php

namespace App\Modules\CostAccounting\Presentation\Http\Controllers;

use App\Http\Controllers\Controller; use App\Modules\CostAccounting\Domain\Models\CostPeriod; use Illuminate\Http\Request; use Inertia\Inertia; use Inertia\Response;
class ShowCostPeriodController extends Controller
{
    public function __invoke(Request $request,CostPeriod $costPeriod):Response
    {
        abort_unless($request->user()->hasPermission('cost-periods.view'),403); $costPeriod->load(['utilities','rates']);
        return Inertia::render('costs/periods/Show',['period'=>[...$costPeriod->only(['id','year','month','processed_flour_quantity']),'label'=>$costPeriod->label(),'status_label'=>$costPeriod->status->label(),'utilities'=>$costPeriod->utilities->map(fn($record)=>[...$record->only(['id','total_amount','business_percentage','household_percentage','business_amount','household_amount','physical_consumption','physical_consumption_unit','reference']),'type'=>$record->utility_type->value,'type_label'=>$record->utility_type->label(),'billed_from'=>$record->billed_from->toDateString(),'billed_to'=>$record->billed_to->toDateString(),'paid_at'=>$record->paid_at->toDateString()]),'rates'=>$costPeriod->rates->map(fn($rate)=>[...$rate->only(['id','suggested_rate','manual_rate','effective_rate','calculation_base_quantity','override_reason']),'type'=>$rate->cost_type->value,'type_label'=>$rate->cost_type->label(),'method'=>$rate->method->value])]]);
    }
}
