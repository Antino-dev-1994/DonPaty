<?php

namespace App\Modules\Dashboard\Application;

use App\Models\User;
use App\Modules\Dashboard\Application\Data\ReportDateRange;
use App\Modules\Household\Application\HouseholdBudgetReport;
use App\Modules\Household\Application\HouseholdVisibility;
use App\Modules\Household\Domain\Models\Debt;
use App\Modules\Household\Domain\Models\FundRequest;
use App\Modules\Household\Domain\Models\HouseholdBudget;
use App\Modules\Household\Domain\Models\SavingsGoal;

class HouseholdReportQuery
{
    public function __construct(
        private readonly HouseholdVisibility $visibility,
        private readonly HouseholdBudgetReport $budgets,
    ) {}

    /** @return array<string, mixed> */
    public function execute(ReportDateRange $range, User $viewer): array
    {
        $budget = HouseholdBudget::query()->where('year', $range->from->year)->where('month', $range->from->month)->first();
        $lines = $this->budgets->execute($budget, $viewer);
        $requests = $this->visibility->fundRequests(FundRequest::query(), $viewer)->with('requester:id,name')->whereIn('status', ['requested', 'approved', 'paid'])->orderBy('needed_at')->get();
        $debts = $this->visibility->personalRecords(Debt::query(), $viewer)->with('installments')->where('status', 'active')->get();
        $goals = $this->visibility->personalRecords(SavingsGoal::query(), $viewer)->withSum('contributions', 'amount')->where('status', 'active')->get();

        return [
            'budget' => [
                'month' => $range->from->format('Y-m'),
                'status' => $budget?->status->value,
                'budgeted' => (int) $lines->sum('budgeted_amount'),
                'executed' => (int) $lines->sum('executed_amount'),
                'available' => (int) $lines->sum('available_amount'),
                'lines' => $lines->all(),
            ],
            'pending_requests' => $requests->count(),
            'requests_total' => (int) $requests->sum('amount'),
            'requests' => $requests->map(fn (FundRequest $request) => [
                'id' => $request->id,
                'document' => $request->document_number,
                'requester' => $request->requester->name,
                'needed_at' => $request->needed_at->format('Y-m-d'),
                'amount' => $request->amount,
                'status' => $request->status->label(),
            ])->all(),
            'debt_balance' => (int) $debts->sum(fn (Debt $debt) => $debt->installments->sum(fn ($item) => $item->principal_amount + $item->interest_amount - $item->principal_paid - $item->interest_paid)),
            'overdue_installments' => $debts->flatMap->installments->filter(fn ($item) => $item->status->value !== 'paid' && $item->due_at->isPast())->count(),
            'savings_target' => (int) $goals->sum('target_amount'),
            'savings_saved' => (int) $goals->sum(fn (SavingsGoal $goal) => (int) ($goal->contributions_sum_amount ?? 0)),
        ];
    }
}
