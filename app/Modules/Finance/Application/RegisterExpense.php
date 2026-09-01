<?php

namespace App\Modules\Finance\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\CostAccounting\Domain\Enums\CostPeriodStatus;
use App\Modules\CostAccounting\Domain\Enums\CostType;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use App\Modules\CostAccounting\Domain\Models\CostPoolEntry;
use App\Modules\Finance\Application\Data\JournalEntryData;
use App\Modules\Finance\Application\Data\JournalLineData;
use App\Modules\Finance\Application\Data\RegisterExpenseData;
use App\Modules\Finance\Domain\Enums\BusinessRecordStatus;
use App\Modules\Finance\Domain\Enums\FinancialCategoryType;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Enums\PayableStatus;
use App\Modules\Finance\Domain\Enums\PaymentDirection;
use App\Modules\Finance\Domain\Models\ExpenseRecord;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\FinancialCategory;
use App\Modules\Finance\Domain\Models\Payable;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Facades\DB;

class RegisterExpense
{
    public function __construct(
        private readonly NextDocumentNumber $numbers,
        private readonly RecordBusinessPayment $payments,
        private readonly PostJournalEntry $journal,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(RegisterExpenseData $data): ExpenseRecord
    {
        $this->validateAmounts($data);

        return DB::transaction(function () use ($data): ExpenseRecord {
            $category = FinancialCategory::query()
                ->with('ledgerAccount')
                ->where('record_type', FinancialCategoryType::Expense)
                ->where('scope', FinancialScope::Business)
                ->where('is_active', true)
                ->findOrFail($data->categoryId);
            $balance = $data->totalAmount - $data->initialPaymentAmount;
            if ($balance > 0 && ! $data->personId) {
                throw new DomainException('Un gasto pendiente requiere un tercero para crear la cuenta por pagar.');
            }

            [$period, $linkedPoolEntry] = $this->resolveCostContext($category, $data);
            $record = ExpenseRecord::create([
                'document_number' => $this->numbers->execute('expense_record', 'GAS', $data->effectiveAt),
                'financial_category_id' => $category->id,
                'person_id' => $data->personId,
                'cost_period_id' => $period?->id,
                'cost_pool_entry_id' => $linkedPoolEntry?->id,
                'effective_at' => $data->effectiveAt,
                'due_at' => $data->dueAt,
                'description' => trim($data->description),
                'total_amount' => $data->totalAmount,
                'paid_amount' => $data->initialPaymentAmount,
                'balance_amount' => $balance,
                'status' => $this->status($data->initialPaymentAmount, $balance),
                'created_by' => $data->creator->id,
            ]);

            $payment = null;
            if ($data->initialPaymentAmount > 0) {
                $payment = $this->payments->execute(
                    PaymentDirection::Outgoing,
                    $data->initialPaymentAmount,
                    $data->financialAccountId ?? throw new DomainException('Selecciona la cuenta desde la que se pagó.'),
                    $data->personId,
                    $data->effectiveAt,
                    $data->paymentReference,
                    $data->creator,
                    $record,
                );
            }
            if ($balance > 0) {
                Payable::create([
                    'document_number' => $this->numbers->execute('payable', 'CXP', $data->effectiveAt),
                    'person_id' => $data->personId,
                    'source_type' => $record->getMorphClass(),
                    'source_id' => $record->id,
                    'issued_at' => $data->effectiveAt,
                    'due_at' => $data->dueAt,
                    'original_amount' => $balance,
                    'balance_amount' => $balance,
                    'status' => PayableStatus::Pending,
                ]);
            }
            if ($category->cost_type === CostType::Labor && $payment && $period) {
                $period->poolEntries()->create([
                    'cost_type' => CostType::Labor,
                    'amount' => $payment->amount,
                    'source_type' => $payment->getMorphClass(),
                    'source_id' => $payment->id,
                    'effective_at' => $payment->paid_at,
                ]);
            }

            $lines = [new JournalLineData($category->ledger_account_id, $data->totalAmount, 0, $data->personId, $data->description)];
            if ($payment) $lines[] = new JournalLineData($payment->financial_account_id, 0, $data->initialPaymentAmount, $data->personId, 'Dinero pagado');
            if ($balance > 0) $lines[] = new JournalLineData(FinancialAccount::query()->where('code', '2110-CXP')->sole()->id, 0, $balance, $data->personId, 'Gasto pendiente de pago');
            $entry = $this->journal->execute(new JournalEntryData($data->effectiveAt, "Gasto {$record->document_number}: {$record->description}", $data->creator, $lines, $record));
            $record->update(['journal_entry_id' => $entry->id]);
            $this->audit->execute('finance.expense_registered', $record, $data->creator, after: $record->fresh()->toArray());

            return $record->fresh(['category', 'payable', 'costPoolEntry', 'journalEntry']);
        });
    }

    /** @return array{?CostPeriod, ?CostPoolEntry} */
    private function resolveCostContext(FinancialCategory $category, RegisterExpenseData $data): array
    {
        if (! $category->cost_type) return [null, null];
        if (! $data->costPeriodId) throw new DomainException('Esta categoría requiere un periodo de costos abierto.');
        $period = CostPeriod::query()->where('status', CostPeriodStatus::Open)->findOrFail($data->costPeriodId);
        if ($category->cost_type === CostType::Labor) return [$period, null];

        if (! $data->costPoolEntryId) {
            throw new DomainException('Selecciona la factura del periodo para evitar duplicar el costo real.');
        }
        $entry = CostPoolEntry::query()
            ->where('cost_period_id', $period->id)
            ->where('cost_type', $category->cost_type)
            ->findOrFail($data->costPoolEntryId);
        if ($entry->amount !== $data->totalAmount || ExpenseRecord::query()->where('cost_pool_entry_id', $entry->id)->exists()) {
            throw new DomainException('La factura seleccionada ya fue asociada o no coincide con el valor del gasto.');
        }

        return [$period, $entry];
    }

    private function validateAmounts(RegisterExpenseData $data): void
    {
        if ($data->totalAmount <= 0 || $data->initialPaymentAmount < 0 || $data->initialPaymentAmount > $data->totalAmount) {
            throw new DomainException('El total debe ser positivo y el pago inicial no puede superarlo.');
        }
        if (trim($data->description) === '') throw new DomainException('El gasto requiere una descripción.');
    }

    private function status(int $paid, int $balance): BusinessRecordStatus
    {
        if ($balance === 0) return BusinessRecordStatus::Paid;
        return $paid > 0 ? BusinessRecordStatus::Partial : BusinessRecordStatus::Pending;
    }
}
