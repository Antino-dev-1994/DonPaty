<?php

namespace App\Modules\Household\Application;

use App\Models\User;
use App\Modules\Household\Domain\Enums\HouseholdTransactionType;
use App\Modules\Household\Domain\Models\HouseholdBudget;
use App\Modules\Household\Domain\Models\HouseholdBudgetLine;
use App\Modules\Household\Domain\Models\HouseholdTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class HouseholdBudgetReport
{
    public function __construct(private readonly HouseholdVisibility $visibility) {}

    /** @return Collection<int, array<string, mixed>> */
    public function execute(?HouseholdBudget $budget, User $viewer): Collection
    {
        if (! $budget) {
            return collect();
        }
        $start = Carbon::create($budget->year, $budget->month)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $lines = $budget->lines()->with(['category:id,name', 'person:id,name'])
            ->when(! $this->visibility->canViewAll($viewer), fn ($query) => $query->where(fn ($scope) => $scope->whereNull('person_id')->orWhere('person_id', $viewer->person_id)))
            ->orderBy('created_at')->get();

        return $lines->map(function (HouseholdBudgetLine $line) use ($viewer, $start, $end): array {
            $transactions = $this->visibility->transactions(HouseholdTransaction::query(), $viewer)
                ->where('transaction_type', HouseholdTransactionType::Expense)
                ->where('counts_for_budget', true)
                ->where('financial_category_id', $line->financial_category_id)
                ->whereBetween('occurred_at', [$start, $end]);
            if ($line->person_id) {
                $transactions->where('person_id', $line->person_id);
            }
            $executed = (int) $transactions->sum('amount');

            return [
                ...$line->only(['id', 'budgeted_amount', 'person_id']),
                'category' => $line->category->name,
                'person' => $line->person?->name,
                'executed_amount' => $executed,
                'available_amount' => $line->budgeted_amount - $executed,
                'percentage' => round($executed * 100 / $line->budgeted_amount, 1),
            ];
        });
    }
}
