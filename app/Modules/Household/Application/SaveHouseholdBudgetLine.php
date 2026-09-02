<?php

namespace App\Modules\Household\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Finance\Domain\Enums\FinancialCategoryType;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Models\FinancialCategory;
use App\Modules\Household\Application\Data\SaveHouseholdBudgetLineData;
use App\Modules\Household\Domain\Enums\HouseholdBudgetStatus;
use App\Modules\Household\Domain\Models\HouseholdBudget;
use App\Modules\Household\Domain\Models\HouseholdBudgetLine;
use DomainException;
use Illuminate\Support\Facades\DB;

class SaveHouseholdBudgetLine
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(SaveHouseholdBudgetLineData $data): HouseholdBudgetLine
    {
        if ($data->month < 1 || $data->month > 12 || $data->year < 2020 || $data->budgetedAmount <= 0) {
            throw new DomainException('El periodo y el valor presupuestado deben ser válidos.');
        }
        FinancialCategory::query()->where('scope', FinancialScope::Household)->where('record_type', FinancialCategoryType::Expense)->where('is_active', true)->findOrFail($data->categoryId);

        return DB::transaction(function () use ($data): HouseholdBudgetLine {
            $budget = HouseholdBudget::query()->firstOrCreate(
                ['year' => $data->year, 'month' => $data->month],
                ['status' => HouseholdBudgetStatus::Draft, 'created_by' => $data->actor->id],
            );
            if ($budget->status !== HouseholdBudgetStatus::Draft) {
                throw new DomainException('Un presupuesto confirmado no puede modificarse.');
            }
            $line = HouseholdBudgetLine::query()
                ->where('household_budget_id', $budget->id)
                ->where('financial_category_id', $data->categoryId)
                ->where('person_id', $data->personId)
                ->first();
            $before = $line?->toArray();
            $line ??= new HouseholdBudgetLine([
                'household_budget_id' => $budget->id,
                'financial_category_id' => $data->categoryId,
                'person_id' => $data->personId,
            ]);
            $line->budgeted_amount = $data->budgetedAmount;
            $line->save();
            $this->audit->execute('household.budget_line_saved', $line, $data->actor, $before, $line->fresh()->toArray());

            return $line->fresh(['budget', 'category', 'person']);
        });
    }
}
