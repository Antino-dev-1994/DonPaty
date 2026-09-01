<?php

namespace App\Modules\Finance\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CashManagement\Application\FinancialAccountBalance;
use App\Modules\Finance\Domain\Enums\FinancialAccountType;
use App\Modules\Finance\Domain\Enums\FinancialDocumentStatus;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Enums\PaymentDirection;
use App\Modules\Finance\Domain\Models\ExpenseRecord;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\FinancialCategory;
use App\Modules\Finance\Domain\Models\IncomeRecord;
use App\Modules\Finance\Domain\Models\JournalLine;
use App\Modules\Finance\Domain\Models\Payable;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\People\Domain\Models\Person;
use App\Modules\Sales\Domain\Models\Receivable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class FinanceDashboardController extends Controller
{
    public function __invoke(Request $request, FinancialAccountBalance $balances): Response
    {
        abort_unless($request->user()->hasPermission('finance.view'), 403);
        $month = preg_match('/^\d{4}-\d{2}$/', (string) $request->input('month')) ? $request->string('month')->toString() : now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $payments = Payment::query()->where('status', FinancialDocumentStatus::Confirmed)->whereBetween('paid_at', [$start, $end]);
        $cashIn = (clone $payments)->where('direction', PaymentDirection::Incoming)->sum('amount');
        $cashOut = (clone $payments)->where('direction', PaymentDirection::Outgoing)->sum('amount');
        $result = JournalLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->join('financial_accounts', 'financial_accounts.id', '=', 'journal_lines.financial_account_id')
            ->where('journal_entries.status', FinancialDocumentStatus::Confirmed)
            ->whereBetween('journal_entries.effective_at', [$start, $end]);
        $revenue = (clone $result)->where('financial_accounts.account_type', FinancialAccountType::Revenue)->selectRaw('COALESCE(SUM(journal_lines.credit_amount - journal_lines.debit_amount), 0) as total')->value('total');
        $expenses = (clone $result)->where('financial_accounts.account_type', FinancialAccountType::Expense)->selectRaw('COALESCE(SUM(journal_lines.debit_amount - journal_lines.credit_amount), 0) as total')->value('total');

        $movements = JournalLine::query()->with(['entry:id,document_number,effective_at,description,status', 'account:id,code,name', 'person:id,name'])
            ->whereHas('entry', fn ($query) => $query->where('status', FinancialDocumentStatus::Confirmed)->whereBetween('effective_at', [$start, $end]))
            ->when($request->input('account_id'), fn ($query, $id) => $query->where('financial_account_id', $id))
            ->when($request->input('person_id'), fn ($query, $id) => $query->where('person_id', $id))
            ->when($request->input('category_id'), function ($query, $id): void {
                $accountId = FinancialCategory::query()->whereKey($id)->value('ledger_account_id');
                $query->where('financial_account_id', $accountId);
            })
            ->latest('created_at')->paginate(30)->withQueryString()
            ->through(fn ($line) => [
                'id' => $line->id,
                'document' => $line->entry->document_number,
                'date' => $line->entry->effective_at->format('Y-m-d H:i'),
                'entry_description' => $line->entry->description,
                'account' => "{$line->account->code} · {$line->account->name}",
                'person' => $line->person?->name,
                'debit' => $line->debit_amount,
                'credit' => $line->credit_amount,
            ]);

        $accounts = FinancialAccount::query()->where('scope', FinancialScope::Business)->where('is_active', true)->orderBy('code')->get();

        return Inertia::render('finance/Index', [
            'month' => $month,
            'metrics' => [
                'cash_in' => (int) $cashIn,
                'cash_out' => (int) $cashOut,
                'cash_flow' => (int) $cashIn - (int) $cashOut,
                'revenue' => (int) $revenue,
                'expenses' => (int) $expenses,
                'profit' => (int) $revenue - (int) $expenses,
                'receivables' => (int) Receivable::query()->whereIn('status', ['pending', 'partial'])->sum('balance_amount'),
                'payables' => (int) Payable::query()->whereIn('status', ['pending', 'partial'])->sum('balance_amount'),
            ],
            'accounts' => $accounts->map(function ($account) use ($balances): array {
                $rawBalance = $balances->execute($account->id);
                $creditNature = in_array($account->account_type, [FinancialAccountType::Liability, FinancialAccountType::Equity, FinancialAccountType::Revenue], true);

                return [...$account->only(['id', 'code', 'name']), 'account_type' => $account->account_type->value, 'balance' => $creditNature ? -$rawBalance : $rawBalance];
            }),
            'categories' => FinancialCategory::query()->where('scope', FinancialScope::Business)->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'people' => Person::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['account_id', 'category_id', 'person_id']),
            'movements' => $movements,
            'incomeRecords' => IncomeRecord::query()->with('category:id,name')->whereBetween('effective_at', [$start, $end])->latest('effective_at')->limit(10)->get()->map(fn ($record) => $this->record($record)),
            'expenseRecords' => ExpenseRecord::query()->with('category:id,name')->whereBetween('effective_at', [$start, $end])->latest('effective_at')->limit(10)->get()->map(fn ($record) => $this->record($record)),
            'payables' => Payable::query()->with('person:id,name')->whereIn('status', ['pending', 'partial'])->orderBy('due_at')->limit(20)->get()->map(fn ($payable) => [
                'id' => $payable->id,
                'document_number' => $payable->document_number,
                'person' => $payable->person->name,
                'due_at' => $payable->due_at?->format('Y-m-d'),
                'balance_amount' => $payable->balance_amount,
            ]),
            'canManage' => $request->user()->hasPermission('finance.manage'),
        ]);
    }

    private function record($record): array
    {
        return [...$record->only(['id', 'document_number', 'description', 'total_amount', 'paid_amount', 'balance_amount']), 'category' => $record->category->name, 'date' => $record->effective_at->format('Y-m-d'), 'status' => $record->status->label()];
    }
}
