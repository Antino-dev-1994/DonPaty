<?php

namespace App\Modules\Finance\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Application\Data\RegisterIncomeData;
use App\Modules\Finance\Application\RegisterIncome;
use App\Modules\Finance\Presentation\Http\Requests\StoreIncomeRecordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class StoreIncomeRecordController extends Controller
{
    public function __invoke(StoreIncomeRecordRequest $request, RegisterIncome $action): RedirectResponse
    {
        $data = $request->validated();
        $record = $action->execute(new RegisterIncomeData(
            $data['category_id'],
            $data['person_id'] ?? null,
            Carbon::parse($data['effective_at']),
            isset($data['due_at']) ? Carbon::parse($data['due_at']) : null,
            $data['description'],
            (int) $data['total_amount'],
            (int) $data['initial_payment_amount'],
            $data['financial_account_id'] ?? null,
            $data['payment_reference'] ?? null,
            $request->user(),
        ));

        return to_route('finance.index')->with('success', "Ingreso {$record->document_number} registrado.");
    }
}
