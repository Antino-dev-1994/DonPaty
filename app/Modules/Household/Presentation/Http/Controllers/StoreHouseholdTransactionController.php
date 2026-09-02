<?php

namespace App\Modules\Household\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Household\Application\Data\HouseholdTransactionData;
use App\Modules\Household\Application\RecordHouseholdTransaction;
use App\Modules\Household\Domain\Enums\HouseholdTransactionType;
use App\Modules\Household\Presentation\Http\Requests\StoreHouseholdTransactionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class StoreHouseholdTransactionController extends Controller
{
    public function __invoke(StoreHouseholdTransactionRequest $request, RecordHouseholdTransaction $action): RedirectResponse
    {
        $action->execute(new HouseholdTransactionData(
            HouseholdTransactionType::from($request->string('type')->toString()),
            $request->input('person_id'),
            $request->input('category_id'),
            $request->input('from_account_id'),
            $request->input('to_account_id'),
            Carbon::parse($request->input('occurred_at')),
            $request->integer('amount'),
            $request->string('description')->toString(),
            $request->boolean('counts_for_budget', true),
            $request->user(),
        ));

        return to_route('household.index')->with('success', 'Movimiento del hogar registrado.');
    }
}
