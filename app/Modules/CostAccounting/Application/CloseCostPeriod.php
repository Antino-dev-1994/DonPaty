<?php

namespace App\Modules\CostAccounting\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\CostAccounting\Domain\Enums\CostPeriodStatus;
use App\Modules\CostAccounting\Domain\Enums\CostType;
use App\Modules\CostAccounting\Domain\Models\CostAllocation;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use App\Modules\Finance\Application\Data\JournalEntryData;
use App\Modules\Finance\Application\Data\JournalLineData;
use App\Modules\Finance\Application\PostJournalEntry;
use App\Modules\Finance\Domain\Enums\FinancialCategoryType;
use App\Modules\Finance\Domain\Models\ExpenseRecord;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\FinancialCategory;
use App\Modules\Production\Domain\Enums\ProductionStatus;
use App\Modules\Production\Domain\Models\ProductionOrder;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CloseCostPeriod
{
    public function __construct(
        private readonly PostJournalEntry $journal,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(CostPeriod $period, User $actor): CostPeriod
    {
        return DB::transaction(function () use ($period, $actor): CostPeriod {
            $period = CostPeriod::query()->with('poolEntries')->lockForUpdate()->findOrFail($period->id);
            if ($period->status !== CostPeriodStatus::Open) throw new DomainException('Solo puede cerrarse un periodo abierto.');
            if (ProductionOrder::query()->where('cost_period_id', $period->id)->whereIn('status', [ProductionStatus::Planned, ProductionStatus::InProgress])->exists()) {
                throw new DomainException('Finaliza o revierte las producciones pendientes antes de cerrar el periodo.');
            }
            $unlinkedUtilities = $period->poolEntries
                ->whereIn('cost_type', [CostType::Electricity, CostType::Gas])
                ->filter(fn ($entry) => ! ExpenseRecord::query()->where('cost_pool_entry_id', $entry->id)->exists());
            if ($unlinkedUtilities->isNotEmpty()) {
                throw new DomainException('Registra los gastos de electricidad y gas asociados a las facturas del periodo antes de cerrarlo.');
            }

            $journalLines = [];
            $allocatedTotal = 0;
            foreach (CostType::cases() as $type) {
                $actual = (int) $period->poolEntries->where('cost_type', $type)->sum('amount');
                $allocated = (int) CostAllocation::query()->where('cost_period_id', $period->id)->where('cost_type', $type->value)->whereNull('reversed_at')->sum('amount');
                $period->variances()->updateOrCreate(['cost_type' => $type->value], [
                    'actual_amount' => $actual,
                    'allocated_amount' => $allocated,
                    'variance_amount' => $actual - $allocated,
                ]);
                if ($allocated <= 0) continue;

                $category = FinancialCategory::query()
                    ->where('record_type', FinancialCategoryType::Expense)
                    ->where('cost_type', $type)
                    ->where('is_active', true)
                    ->sole();
                $journalLines[] = new JournalLineData($category->ledger_account_id, 0, $allocated, null, "Costo {$type->label()} aplicado a producción");
                $allocatedTotal += $allocated;
            }

            $entry = null;
            if ($allocatedTotal > 0) {
                array_unshift($journalLines, new JournalLineData(
                    FinancialAccount::query()->where('code', '1435-INVENTARIO')->sole()->id,
                    $allocatedTotal,
                    0,
                    null,
                    'Costos aplicados incorporados al inventario',
                ));
                $effectiveAt = Carbon::create($period->year, $period->month, 1)->endOfMonth()->endOfDay();
                $entry = $this->journal->execute(new JournalEntryData($effectiveAt, "Conciliación de costos {$period->label()}", $actor, $journalLines, $period));
            }

            $period->update([
                'status' => CostPeriodStatus::Closed,
                'closed_at' => now(),
                'closed_by' => $actor->id,
                'closure_journal_entry_id' => $entry?->id,
            ]);
            $this->audit->execute('costs.period_closed', $period, $actor, after: $period->fresh('variances')->toArray());

            return $period->fresh(['variances', 'closureJournalEntry']);
        });
    }
}
