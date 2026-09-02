<?php

namespace App\Modules\Household\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Household\Application\ConfirmHouseholdBudget;
use App\Modules\Household\Domain\Models\HouseholdBudget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConfirmHouseholdBudgetController extends Controller
{
    public function __invoke(Request $request, HouseholdBudget $budget, ConfirmHouseholdBudget $action): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('household.manage'), 403);
        $action->execute($budget, $request->user());

        return to_route('household.index', ['month' => $budget->label()])->with('success', 'Presupuesto confirmado.');
    }
}
