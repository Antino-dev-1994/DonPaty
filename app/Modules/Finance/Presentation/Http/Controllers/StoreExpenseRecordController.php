<?php

namespace App\Modules\Finance\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Application\Data\RegisterExpenseData;
use App\Modules\Finance\Application\RegisterExpense;
use App\Modules\Finance\Presentation\Http\Requests\StoreExpenseRecordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class StoreExpenseRecordController extends Controller
{
    public function __invoke(StoreExpenseRecordRequest $request, RegisterExpense $action): RedirectResponse
    {
        $data = $request->validated();
        $record = $action->execute(new RegisterExpenseData(
            $data['category_id'],
            $data['person_id'] ?? null,
            $data['cost_period_id'] ?? null,
            $data['cost_pool_entry_id'] ?? null,
            Carbon::parse($data['effective_at']),
            isset($data['due_at']) ? Carbon::parse($data['due_at']) : null,
            $data['description'],
            (int) $data['total_amount'],
            (int) $data['initial_payment_amount'],
            $data['financial_account_id'] ?? null,
            $data['payment_reference'] ?? null,
            $request->user(),
        ));

        return to_route('finance.index')->with('success', "Gasto {$record->document_number} registrado.");
    }
}
