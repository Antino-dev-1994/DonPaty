<?php

namespace App\Modules\Household\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Household\Application\ConfirmFundRequest;
use App\Modules\Household\Domain\Models\FundRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConfirmFundRequestController extends Controller
{
    public function __invoke(Request $request, FundRequest $fundRequest, ConfirmFundRequest $action): RedirectResponse
    {
        $action->execute($fundRequest, $request->user());

        return to_route('household.index')->with('success', 'Recepción confirmada.');
    }
}
