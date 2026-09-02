<?php

namespace App\Modules\Household\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Household\Application\ContributeToSavingsGoal;
use App\Modules\Household\Application\Data\ContributeToSavingsGoalData;
use App\Modules\Household\Domain\Models\SavingsGoal;
use App\Modules\Household\Presentation\Http\Requests\StoreSavingsContributionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class StoreSavingsContributionController extends Controller
{
    public function __invoke(StoreSavingsContributionRequest $request, SavingsGoal $savingsGoal, ContributeToSavingsGoal $action): RedirectResponse
    {
        $action->execute($savingsGoal, new ContributeToSavingsGoalData(
            $request->string('source_account_id')->toString(),
            $request->integer('amount'),
            Carbon::parse($request->input('contributed_at')),
            $request->user(),
        ));

        return to_route('household.index')->with('success', 'Aporte de ahorro registrado.');
    }
}
