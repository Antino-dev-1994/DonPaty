<?php

namespace App\Modules\Household\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Household\Application\CreateSavingsGoal;
use App\Modules\Household\Application\Data\CreateSavingsGoalData;
use App\Modules\Household\Presentation\Http\Requests\StoreSavingsGoalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class StoreSavingsGoalController extends Controller
{
    public function __invoke(StoreSavingsGoalRequest $request, CreateSavingsGoal $action): RedirectResponse
    {
        $action->execute(new CreateSavingsGoalData(
            $request->string('name')->toString(),
            $request->input('person_id'),
            $request->string('financial_account_id')->toString(),
            $request->integer('target_amount'),
            $request->filled('target_date') ? Carbon::parse($request->input('target_date')) : null,
            $request->user(),
        ));

        return to_route('household.index')->with('success', 'Meta de ahorro creada.');
    }
}
