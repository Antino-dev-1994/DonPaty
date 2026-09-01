<?php

namespace App\Modules\CostAccounting\Presentation\Http\Controllers;

use App\Http\Controllers\Controller; use App\Modules\CostAccounting\Domain\Enums\UtilityType; use App\Modules\CostAccounting\Domain\Models\CostPeriod; use Illuminate\Http\Request; use Illuminate\Support\Carbon; use Inertia\Inertia; use Inertia\Response;
class CreateCostPeriodController extends Controller
{
    public function __invoke(Request $request):Response
    {
        abort_unless($request->user()->hasPermission('cost-periods.manage'),403); $target=Carbon::now()->startOfMonth(); while(CostPeriod::query()->where('year',$target->year)->where('month',$target->month)->exists())$target->addMonth(); $previous=$target->copy()->subMonth(); $previousPeriod=CostPeriod::query()->where('year',$previous->year)->where('month',$previous->month)->first();
        return Inertia::render('costs/periods/Create',['target'=>['year'=>$target->year,'month'=>$target->month,'label'=>$target->translatedFormat('F Y')],'previousFlourQuantity'=>$previousPeriod?->processed_flour_quantity??'0','utilityTypes'=>collect(UtilityType::cases())->map(fn($type)=>['value'=>$type->value,'label'=>$type->label(),'consumption_unit'=>$type->consumptionUnit()]),'previousMonth'=>['from'=>$previous->copy()->startOfMonth()->toDateString(),'to'=>$previous->copy()->endOfMonth()->toDateString()]]);
    }
}
