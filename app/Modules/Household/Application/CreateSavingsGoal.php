<?php

namespace App\Modules\Household\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Household\Application\Data\CreateSavingsGoalData;
use App\Modules\Household\Domain\Enums\SavingsGoalStatus;
use App\Modules\Household\Domain\Models\SavingsGoal;
use DomainException;

class CreateSavingsGoal
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(CreateSavingsGoalData $data): SavingsGoal
    {
        if ($data->targetAmount <= 0 || trim($data->name) === '') {
            throw new DomainException('La meta requiere nombre y valor objetivo positivo.');
        }
        $account = FinancialAccount::query()->whereIn('scope', [FinancialScope::Household, FinancialScope::Personal])->where('is_active', true)->findOrFail($data->financialAccountId);
        if ($account->scope === FinancialScope::Personal && $account->person_id !== $data->personId) {
            throw new DomainException('La cuenta reservada no pertenece al habitante indicado.');
        }
        $goal = SavingsGoal::create([
            'name' => trim($data->name),
            'person_id' => $data->personId,
            'financial_account_id' => $account->id,
            'target_amount' => $data->targetAmount,
            'target_date' => $data->targetDate,
            'status' => SavingsGoalStatus::Active,
            'created_by' => $data->creator->id,
        ]);
        $this->audit->execute('household.savings_goal_created', $goal, $data->creator, after: $goal->toArray());

        return $goal->fresh(['person', 'financialAccount']);
    }
}
