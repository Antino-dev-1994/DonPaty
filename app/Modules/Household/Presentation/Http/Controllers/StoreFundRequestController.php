<?php

namespace App\Modules\Household\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Household\Application\CreateFundRequest;
use App\Modules\Household\Application\Data\CreateFundRequestData;
use App\Modules\Household\Presentation\Http\Requests\StoreFundRequestRequest;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class StoreFundRequestController extends Controller
{
    public function __invoke(StoreFundRequestRequest $request, CreateFundRequest $action): RedirectResponse
    {
        $requesterId = $request->user()->hasPermission('household.manage')
            ? $request->input('requester_person_id')
            : $request->user()->person_id;
        if (! $requesterId) {
            throw new DomainException('El usuario debe estar asociado a un habitante.');
        }
        $action->execute(new CreateFundRequestData(
            $requesterId,
            FinancialScope::from($request->string('source_scope')->toString()),
            $request->integer('amount'),
            $request->string('reason')->toString(),
            Carbon::parse($request->input('needed_at')),
            $request->user(),
        ));

        return to_route('household.index')->with('success', 'Solicitud de fondos creada.');
    }
}
