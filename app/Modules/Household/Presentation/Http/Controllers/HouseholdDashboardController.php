<?php

namespace App\Modules\Household\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CashManagement\Application\FinancialAccountBalance;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\FinancialCategory;
use App\Modules\Household\Application\HouseholdVisibility;
use App\Modules\Household\Domain\Enums\HouseholdTransactionType;
use App\Modules\Household\Domain\Models\HouseholdTransaction;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class HouseholdDashboardController extends Controller
{
    public function __invoke(Request $request, HouseholdVisibility $visibility, FinancialAccountBalance $balances): Response
    {
        $month = preg_match('/^\d{4}-\d{2}$/', (string) $request->input('month'))
            ? $request->string('month')->toString()
            : now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $visibleTransactions = $visibility->transactions(HouseholdTransaction::query(), $request->user());
        $monthTransactions = (clone $visibleTransactions)->whereBetween('occurred_at', [$start, $end]);
        $income = (clone $monthTransactions)->where('transaction_type', HouseholdTransactionType::Income)->sum('amount');
        $expenses = (clone $monthTransactions)->where('transaction_type', HouseholdTransactionType::Expense)->sum('amount');
        $accounts = $visibility->accounts(
            FinancialAccount::query()
                ->whereIn('scope', [FinancialScope::Household, FinancialScope::Personal])
                ->where('is_active', true),
            $request->user(),
        )->with('person:id,name')->orderBy('name')->get();

        return Inertia::render('household/Index', [
            'month' => $month,
            'metrics' => [
                'income' => (int) $income,
                'expenses' => (int) $expenses,
                'result' => (int) $income - (int) $expenses,
                'available' => (int) $accounts->sum(fn (FinancialAccount $account) => $balances->execute($account->id)),
            ],
            'accounts' => $accounts->map(fn (FinancialAccount $account) => [
                ...$account->only(['id', 'code', 'name']),
                'scope' => $account->scope->value,
                'person' => $account->person?->name,
                'person_id' => $account->person_id,
                'balance' => $balances->execute($account->id),
            ]),
            'transactions' => (clone $monthTransactions)->with(['person:id,name', 'category:id,name', 'fromAccount:id,name', 'toAccount:id,name'])
                ->latest('occurred_at')->limit(100)->get()->map(fn (HouseholdTransaction $transaction) => [
                    ...$transaction->only(['id', 'document_number', 'amount', 'description', 'counts_for_budget']),
                    'type' => $transaction->transaction_type->value,
                    'type_label' => $transaction->transaction_type->label(),
                    'date' => $transaction->occurred_at->format('Y-m-d H:i'),
                    'person' => $transaction->person?->name,
                    'category' => $transaction->category?->name,
                    'from_account' => $transaction->fromAccount?->name,
                    'to_account' => $transaction->toAccount?->name,
                ]),
            'categories' => FinancialCategory::query()->where('scope', FinancialScope::Household)->where('is_active', true)->orderBy('name')->get()->map(fn ($category) => [
                ...$category->only(['id', 'name']),
                'type' => $category->record_type->value,
            ]),
            'people' => Person::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'canManage' => $request->user()->hasPermission('household.manage'),
            'canViewAll' => $visibility->canViewAll($request->user()),
            'now' => now()->format('Y-m-d\TH:i'),
        ]);
    }
}
