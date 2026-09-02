<?php

namespace App\Modules\Household\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Household\Application\DecideFundRequest;
use App\Modules\Household\Domain\Enums\FundRequestStatus;
use App\Modules\Household\Domain\Models\FundRequest;
use App\Modules\Household\Presentation\Http\Requests\DecideFundRequestRequest;
use Illuminate\Http\RedirectResponse;

class ApproveFundRequestController extends Controller
{
    public function __invoke(DecideFundRequestRequest $request, FundRequest $fundRequest, DecideFundRequest $action): RedirectResponse
    {
        $action->execute($fundRequest, FundRequestStatus::Approved, $request->user(), $request->input('notes'));

        return to_route('household.index')->with('success', 'Solicitud aprobada.');
    }
}
