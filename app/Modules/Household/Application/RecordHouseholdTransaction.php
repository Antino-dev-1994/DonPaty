<?php

namespace App\Modules\Household\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\CashManagement\Application\FinancialAccountBalance;
use App\Modules\Finance\Application\Data\JournalEntryData;
use App\Modules\Finance\Application\Data\JournalLineData;
use App\Modules\Finance\Application\PostJournalEntry;
use App\Modules\Finance\Domain\Enums\FinancialCategoryType;
use App\Modules\Finance\Domain\Enums\FinancialDocumentStatus;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Enums\PaymentDirection;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\FinancialCategory;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\Household\Application\Data\HouseholdTransactionData;
use App\Modules\Household\Domain\Enums\HouseholdTransactionType;
use App\Modules\Household\Domain\Models\HouseholdTransaction;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Facades\DB;

class RecordHouseholdTransaction
{
    public function __construct(
        private readonly NextDocumentNumber $numbers,
        private readonly PostJournalEntry $journal,
        private readonly FinancialAccountBalance $balances,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(HouseholdTransactionData $data): HouseholdTransaction
    {
        if ($data->amount <= 0 || trim($data->description) === '') {
            throw new DomainException('El movimiento requiere valor positivo y descripción.');
        }

        return DB::transaction(function () use ($data): HouseholdTransaction {
            [$from, $to, $category] = $this->resolveParticipants($data);
            $payment = $this->createPayment($data, $from, $to);
            $entry = $this->journal->execute(new JournalEntryData(
                $data->occurredAt,
                $data->description,
                $data->creator,
                $this->journalLines($data, $from, $to, $category),
                $payment,
            ));
            $transaction = HouseholdTransaction::create([
                'document_number' => $this->numbers->execute('household_transaction', 'HOG', $data->occurredAt),
                'transaction_type' => $data->type,
                'person_id' => $data->personId,
                'financial_category_id' => $category?->id,
                'from_account_id' => $from?->id,
                'to_account_id' => $to?->id,
                'occurred_at' => $data->occurredAt,
                'amount' => $data->amount,
                'description' => trim($data->description),
                'counts_for_budget' => $data->type === HouseholdTransactionType::Expense && $data->countsForBudget,
                'journal_entry_id' => $entry->id,
                'created_by' => $data->creator->id,
            ]);
            if ($payment) {
                $payment->allocations()->create([
                    'allocatable_type' => $transaction->getMorphClass(),
                    'allocatable_id' => $transaction->id,
                    'amount' => $payment->amount,
                ]);
            } else {
                $entry->update(['source_type' => $transaction->getMorphClass(), 'source_id' => $transaction->id]);
            }
            $this->audit->execute('household.transaction_posted', $transaction, $data->creator, after: $transaction->toArray());

            return $transaction->fresh(['category', 'fromAccount', 'toAccount', 'journalEntry']);
        });
    }

    /** @return array{?FinancialAccount, ?FinancialAccount, ?FinancialCategory} */
    private function resolveParticipants(HouseholdTransactionData $data): array
    {
        $accountQuery = fn () => FinancialAccount::query()->whereIn('scope', [FinancialScope::Household, FinancialScope::Personal])->where('is_active', true);
        $from = $data->fromAccountId ? $accountQuery()->findOrFail($data->fromAccountId) : null;
        $to = $data->toAccountId ? $accountQuery()->findOrFail($data->toAccountId) : null;
        $category = null;

        if ($data->type === HouseholdTransactionType::Transfer) {
            if (! $from || ! $to || $from->id === $to->id || $data->categoryId) throw new DomainException('La transferencia requiere cuentas distintas y no usa categoría.');
        } else {
            $expectedType = $data->type === HouseholdTransactionType::Income ? FinancialCategoryType::Income : FinancialCategoryType::Expense;
            $category = FinancialCategory::query()->where('scope', FinancialScope::Household)->where('record_type', $expectedType)->where('is_active', true)->findOrFail($data->categoryId);
            if ($data->type === HouseholdTransactionType::Income && (! $to || $from)) throw new DomainException('El ingreso requiere únicamente una cuenta de destino.');
            if ($data->type === HouseholdTransactionType::Expense && (! $from || $to)) throw new DomainException('El gasto requiere únicamente una cuenta de origen.');
        }
        if ($from && $this->balances->execute($from->id) < $data->amount) throw new DomainException('La cuenta de origen no tiene saldo suficiente.');
        foreach ([$from, $to] as $account) {
            if ($account?->scope === FinancialScope::Personal && $data->personId && $account->person_id !== $data->personId) {
                throw new DomainException('La cuenta personal no pertenece al habitante indicado.');
            }
        }

        return [$from, $to, $category];
    }

    private function createPayment(HouseholdTransactionData $data, ?FinancialAccount $from, ?FinancialAccount $to): ?Payment
    {
        if ($data->type === HouseholdTransactionType::Transfer) return null;
        $direction = $data->type === HouseholdTransactionType::Income ? PaymentDirection::Incoming : PaymentDirection::Outgoing;
        $account = $to ?? $from;

        return Payment::create([
            'document_number' => $this->numbers->execute('household_payment', $direction === PaymentDirection::Incoming ? 'HIN' : 'HEG', $data->occurredAt),
            'direction' => $direction,
            'person_id' => $data->personId,
            'paid_at' => $data->occurredAt,
            'amount' => $data->amount,
            'financial_account_id' => $account->id,
            'status' => FinancialDocumentStatus::Confirmed,
            'reference' => $data->description,
            'created_by' => $data->creator->id,
        ]);
    }

    /** @return list<JournalLineData> */
    private function journalLines(HouseholdTransactionData $data, ?FinancialAccount $from, ?FinancialAccount $to, ?FinancialCategory $category): array
    {
        return match ($data->type) {
            HouseholdTransactionType::Income => [
                new JournalLineData($to->id, $data->amount, 0, $data->personId, 'Entrada al hogar'),
                new JournalLineData($category->ledger_account_id, 0, $data->amount, $data->personId, $data->description),
            ],
            HouseholdTransactionType::Expense => [
                new JournalLineData($category->ledger_account_id, $data->amount, 0, $data->personId, $data->description),
                new JournalLineData($from->id, 0, $data->amount, $data->personId, 'Salida del hogar'),
            ],
            HouseholdTransactionType::Transfer => [
                new JournalLineData($to->id, $data->amount, 0, $data->personId, 'Entrada por transferencia interna'),
                new JournalLineData($from->id, 0, $data->amount, $data->personId, 'Salida por transferencia interna'),
            ],
        };
    }
}
