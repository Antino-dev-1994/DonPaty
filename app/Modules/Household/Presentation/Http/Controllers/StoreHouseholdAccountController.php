<?php

namespace App\Modules\Household\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Household\Application\CreateHouseholdAccount;
use App\Modules\Household\Application\Data\CreateHouseholdAccountData;
use App\Modules\Household\Presentation\Http\Requests\StoreHouseholdAccountRequest;
use Illuminate\Http\RedirectResponse;

class StoreHouseholdAccountController extends Controller
{
    public function __invoke(StoreHouseholdAccountRequest $request, CreateHouseholdAccount $action): RedirectResponse
    {
        $action->execute(new CreateHouseholdAccountData(
            $request->string('name')->toString(),
            FinancialScope::from($request->string('scope')->toString()),
            $request->input('person_id'),
            $request->integer('opening_balance'),
            $request->user(),
        ));

        return to_route('household.index')->with('success', 'Cuenta del hogar creada.');
    }
}
