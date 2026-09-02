<?php

namespace App\Modules\Household\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Household\Application\Data\PayFundRequestData;
use App\Modules\Household\Application\PayFundRequest;
use App\Modules\Household\Domain\Models\FundRequest;
use App\Modules\Household\Presentation\Http\Requests\PayFundRequestRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class PayFundRequestController extends Controller
{
    public function __invoke(PayFundRequestRequest $request, FundRequest $fundRequest, PayFundRequest $action): RedirectResponse
    {
        $action->execute($fundRequest, new PayFundRequestData(
            $request->string('source_account_id')->toString(),
            $request->string('destination_account_id')->toString(),
            Carbon::parse($request->input('paid_at')),
            $request->user(),
        ));

        return to_route('household.index')->with('success', 'Solicitud pagada.');
    }
}
