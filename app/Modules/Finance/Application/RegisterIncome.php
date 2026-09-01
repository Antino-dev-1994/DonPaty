<?php

namespace App\Modules\Finance\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Finance\Application\Data\JournalEntryData;
use App\Modules\Finance\Application\Data\JournalLineData;
use App\Modules\Finance\Application\Data\RegisterIncomeData;
use App\Modules\Finance\Domain\Enums\BusinessRecordStatus;
use App\Modules\Finance\Domain\Enums\FinancialCategoryType;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Enums\PaymentDirection;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\FinancialCategory;
use App\Modules\Finance\Domain\Models\IncomeRecord;
use App\Modules\Sales\Domain\Enums\ReceivableStatus;
use App\Modules\Sales\Domain\Models\Receivable;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Facades\DB;

class RegisterIncome
{
    public function __construct(
        private readonly NextDocumentNumber $numbers,
        private readonly RecordBusinessPayment $payments,
        private readonly PostJournalEntry $journal,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(RegisterIncomeData $data): IncomeRecord
    {
        $this->validateAmounts($data);

        return DB::transaction(function () use ($data): IncomeRecord {
            $category = FinancialCategory::query()
                ->with('ledgerAccount')
                ->where('record_type', FinancialCategoryType::Income)
                ->where('scope', FinancialScope::Business)
                ->where('is_active', true)
                ->findOrFail($data->categoryId);
            $balance = $data->totalAmount - $data->initialPaymentAmount;
            if ($balance > 0 && ! $data->personId) {
                throw new DomainException('Un ingreso pendiente requiere un tercero para crear la cuenta por cobrar.');
            }

            $record = IncomeRecord::create([
                'document_number' => $this->numbers->execute('income_record', 'ING', $data->effectiveAt),
                'financial_category_id' => $category->id,
                'person_id' => $data->personId,
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
                    PaymentDirection::Incoming,
                    $data->initialPaymentAmount,
                    $data->financialAccountId ?? throw new DomainException('Selecciona la cuenta que recibió el dinero.'),
                    $data->personId,
                    $data->effectiveAt,
                    $data->paymentReference,
                    $data->creator,
                    $record,
                );
            }
            if ($balance > 0) {
                Receivable::create([
                    'document_number' => $this->numbers->execute('receivable', 'CXC', $data->effectiveAt),
                    'person_id' => $data->personId,
                    'source_type' => $record->getMorphClass(),
                    'source_id' => $record->id,
                    'issued_at' => $data->effectiveAt,
                    'due_at' => $data->dueAt,
                    'original_amount' => $balance,
                    'balance_amount' => $balance,
                    'status' => ReceivableStatus::Pending,
                ]);
            }

            $lines = [];
            if ($payment) $lines[] = new JournalLineData($payment->financial_account_id, $data->initialPaymentAmount, 0, $data->personId, 'Dinero recibido');
            if ($balance > 0) $lines[] = new JournalLineData(FinancialAccount::query()->where('code', '1305-CXC')->sole()->id, $balance, 0, $data->personId, 'Ingreso pendiente de cobro');
            $lines[] = new JournalLineData($category->ledger_account_id, 0, $data->totalAmount, $data->personId, $data->description);
            $entry = $this->journal->execute(new JournalEntryData($data->effectiveAt, "Ingreso {$record->document_number}: {$record->description}", $data->creator, $lines, $record));
            $record->update(['journal_entry_id' => $entry->id]);
            $this->audit->execute('finance.income_registered', $record, $data->creator, after: $record->fresh()->toArray());

            return $record->fresh(['category', 'receivable', 'journalEntry']);
        });
    }

    private function validateAmounts(RegisterIncomeData $data): void
    {
        if ($data->totalAmount <= 0 || $data->initialPaymentAmount < 0 || $data->initialPaymentAmount > $data->totalAmount) {
            throw new DomainException('El total debe ser positivo y el pago inicial no puede superarlo.');
        }
        if (trim($data->description) === '') throw new DomainException('El ingreso requiere una descripción.');
    }

    private function status(int $paid, int $balance): BusinessRecordStatus
    {
        if ($balance === 0) return BusinessRecordStatus::Paid;
        return $paid > 0 ? BusinessRecordStatus::Partial : BusinessRecordStatus::Pending;
    }
}
