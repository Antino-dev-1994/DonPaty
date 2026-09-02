<?php

namespace App\Modules\Household\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\CashManagement\Application\FinancialAccountBalance;
use App\Modules\Finance\Application\Data\JournalEntryData;
use App\Modules\Finance\Application\Data\JournalLineData;
use App\Modules\Finance\Application\PostJournalEntry;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Household\Application\Data\ContributeToSavingsGoalData;
use App\Modules\Household\Domain\Enums\SavingsGoalStatus;
use App\Modules\Household\Domain\Models\SavingsContribution;
use App\Modules\Household\Domain\Models\SavingsGoal;
use DomainException;
use Illuminate\Support\Facades\DB;

class ContributeToSavingsGoal
{
    public function __construct(
        private readonly FinancialAccountBalance $balances,
        private readonly PostJournalEntry $journal,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(SavingsGoal $goal, ContributeToSavingsGoalData $data): SavingsContribution
    {
        if ($data->amount <= 0) {
            throw new DomainException('El aporte debe ser positivo.');
        }

        return DB::transaction(function () use ($goal, $data): SavingsContribution {
            $goal = SavingsGoal::query()->lockForUpdate()->findOrFail($goal->id);
            if ($goal->status !== SavingsGoalStatus::Active) {
                throw new DomainException('Solo se puede aportar a una meta activa.');
            }
            $source = FinancialAccount::query()->whereIn('scope', [FinancialScope::Household, FinancialScope::Personal])->where('is_active', true)->findOrFail($data->sourceAccountId);
            if ($source->id === $goal->financial_account_id) {
                throw new DomainException('La cuenta de origen debe ser distinta a la cuenta reservada.');
            }
            if ($this->balances->execute($source->id) < $data->amount) {
                throw new DomainException('La cuenta de origen no tiene saldo suficiente.');
            }
            $entry = $this->journal->execute(new JournalEntryData(
                $data->contributedAt,
                "Aporte a meta {$goal->name}",
                $data->actor,
                [
                    new JournalLineData($goal->financial_account_id, $data->amount, 0, $goal->person_id, 'Dinero reservado'),
                    new JournalLineData($source->id, 0, $data->amount, $goal->person_id, 'Traslado a ahorro'),
                ],
            ));
            $contribution = SavingsContribution::create([
                'savings_goal_id' => $goal->id,
                'source_account_id' => $source->id,
                'amount' => $data->amount,
                'contributed_at' => $data->contributedAt,
                'journal_entry_id' => $entry->id,
                'created_by' => $data->actor->id,
            ]);
            $entry->update(['source_type' => $contribution->getMorphClass(), 'source_id' => $contribution->id]);
            if ($goal->contributions()->sum('amount') >= $goal->target_amount) {
                $goal->update(['status' => SavingsGoalStatus::Completed]);
            }
            $this->audit->execute('household.savings_contribution_posted', $contribution, $data->actor, after: $contribution->toArray());

            return $contribution->fresh(['goal', 'sourceAccount', 'journalEntry']);
        });
    }
}
