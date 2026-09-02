<?php

namespace App\Modules\Household\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Household\Application\Data\PayDebtData;
use App\Modules\Household\Application\PayDebt;
use App\Modules\Household\Domain\Models\Debt;
use App\Modules\Household\Presentation\Http\Requests\PayDebtRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class PayDebtController extends Controller
{
    public function __invoke(PayDebtRequest $request, Debt $debt, PayDebt $action): RedirectResponse
    {
        $action->execute($debt, new PayDebtData(
            $request->integer('amount'),
            $request->string('financial_account_id')->toString(),
            Carbon::parse($request->input('paid_at')),
            $request->user(),
        ));

        return to_route('household.index')->with('success', 'Abono registrado.');
    }
}
