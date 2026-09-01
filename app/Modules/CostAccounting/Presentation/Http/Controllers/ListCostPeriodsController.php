<?php

namespace App\Modules\CostAccounting\Presentation\Http\Controllers;

use App\Http\Controllers\Controller; use App\Modules\CostAccounting\Domain\Models\CostPeriod; use Illuminate\Http\Request; use Inertia\Inertia; use Inertia\Response;
class ListCostPeriodsController extends Controller
{
    public function __invoke(Request $request):Response
    {
        abort_unless($request->user()->hasPermission('cost-periods.view'),403);
        return Inertia::render('costs/periods/Index',['periods'=>CostPeriod::query()->withCount('utilities')->orderByDesc('year')->orderByDesc('month')->paginate(24)->through(fn(CostPeriod $period)=>[...$period->only(['id','year','month','processed_flour_quantity','utilities_count']),'label'=>$period->label(),'status'=>$period->status->value,'status_label'=>$period->status->label()]),'canManage'=>$request->user()->hasPermission('cost-periods.manage')]);
    }
}
