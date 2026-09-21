<?php

namespace App\Modules\Finance\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Application\Data\RegisterBusinessOpeningData;
use App\Modules\Finance\Application\RegisterBusinessOpening;
use App\Modules\Finance\Presentation\Http\Requests\StoreBusinessOpeningRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class StoreBusinessOpeningController extends Controller
{
    public function __invoke(StoreBusinessOpeningRequest $request, RegisterBusinessOpening $opening): RedirectResponse
    {
        $data = $request->validated();
        $opening->execute(new RegisterBusinessOpeningData(
            Carbon::parse($data['opened_on'])->startOfDay(),
            (int) $data['cash_amount'],
            (int) $data['nequi_amount'],
            $data['notes'] ?? null,
            $request->user(),
        ));

        return to_route('finance.index')->with('success', 'Apertura inicial del negocio registrada.');
    }
}
