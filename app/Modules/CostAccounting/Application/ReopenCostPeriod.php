<?php

namespace App\Modules\CostAccounting\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\CostAccounting\Domain\Enums\CostPeriodStatus;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use App\Modules\Finance\Application\Data\JournalEntryData;
use App\Modules\Finance\Application\Data\JournalLineData;
use App\Modules\Finance\Application\PostJournalEntry;
use App\Modules\Finance\Domain\Enums\FinancialDocumentStatus;
use DomainException;
use Illuminate\Support\Facades\DB;

class ReopenCostPeriod
{
    public function __construct(private readonly PostJournalEntry $journal, private readonly RecordAuditEvent $audit) {}

    public function execute(CostPeriod $period, string $reason, User $actor): CostPeriod
    {
        if (trim($reason) === '') throw new DomainException('La reapertura requiere un motivo.');

        return DB::transaction(function () use ($period, $reason, $actor): CostPeriod {
            $period = CostPeriod::query()->with('closureJournalEntry.lines')->lockForUpdate()->findOrFail($period->id);
            if ($period->status !== CostPeriodStatus::Closed) throw new DomainException('Solo puede reabrirse un periodo cerrado.');
            $reversal = null;
            if ($period->closureJournalEntry) {
                $reversal = $this->journal->execute(new JournalEntryData(
                    now(),
                    "Reapertura {$period->label()}: {$reason}",
                    $actor,
                    $period->closureJournalEntry->lines->map(fn ($line) => new JournalLineData($line->financial_account_id, $line->credit_amount, $line->debit_amount, $line->person_id, 'Reversión de conciliación'))->all(),
                    $period,
                ));
                $reversal->update(['reversal_of_id' => $period->closure_journal_entry_id]);
                $period->closureJournalEntry->update(['status' => FinancialDocumentStatus::Reversed]);
            }
            $period->variances()->delete();
            $period->update([
                'status' => CostPeriodStatus::Open,
                'reopened_at' => now(),
                'reopened_by' => $actor->id,
                'reopening_journal_entry_id' => $reversal?->id,
                'closed_at' => null,
                'closed_by' => null,
            ]);
            $this->audit->execute('costs.period_reopened', $period, $actor, after: ['reason' => $reason, 'reversal_entry_id' => $reversal?->id]);

            return $period->fresh();
        });
    }
}
