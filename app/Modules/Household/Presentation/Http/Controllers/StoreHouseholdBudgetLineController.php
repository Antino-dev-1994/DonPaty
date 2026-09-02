<?php

namespace App\Modules\Household\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Household\Application\Data\SaveHouseholdBudgetLineData;
use App\Modules\Household\Application\SaveHouseholdBudgetLine;
use App\Modules\Household\Presentation\Http\Requests\StoreHouseholdBudgetLineRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class StoreHouseholdBudgetLineController extends Controller
{
    public function __invoke(StoreHouseholdBudgetLineRequest $request, SaveHouseholdBudgetLine $action): RedirectResponse
    {
        $period = Carbon::createFromFormat('Y-m', $request->string('month')->toString());
        $action->execute(new SaveHouseholdBudgetLineData(
            $period->year,
            $period->month,
            $request->string('category_id')->toString(),
            $request->input('person_id'),
            $request->integer('budgeted_amount'),
            $request->user(),
        ));

        return to_route('household.index', ['month' => $request->input('month')])->with('success', 'Línea presupuestal guardada.');
    }
}
