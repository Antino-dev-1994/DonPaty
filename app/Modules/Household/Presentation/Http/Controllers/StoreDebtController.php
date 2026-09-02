<?php

namespace App\Modules\Household\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Household\Application\CreateDebt;
use App\Modules\Household\Application\Data\CreateDebtData;
use App\Modules\Household\Domain\Enums\DebtDirection;
use App\Modules\Household\Presentation\Http\Requests\StoreDebtRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class StoreDebtController extends Controller
{
    public function __invoke(StoreDebtRequest $request, CreateDebt $action): RedirectResponse
    {
        $action->execute(new CreateDebtData(
            DebtDirection::from($request->string('direction')->toString()),
            $request->input('person_id'),
            $request->string('description')->toString(),
            $request->integer('principal_amount'),
            $request->float('annual_interest_rate'),
            Carbon::parse($request->input('start_date')),
            Carbon::parse($request->input('first_due_at')),
            $request->integer('installment_count'),
            $request->string('financial_account_id')->toString(),
            $request->user(),
        ));

        return to_route('household.index')->with('success', 'Deuda o préstamo registrado.');
    }
}
