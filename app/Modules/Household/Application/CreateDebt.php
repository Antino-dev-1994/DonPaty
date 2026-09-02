<?php

namespace App\Modules\Household\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Finance\Application\Data\JournalEntryData;
use App\Modules\Finance\Application\Data\JournalLineData;
use App\Modules\Finance\Application\PostJournalEntry;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Enums\PaymentDirection;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Household\Application\Data\CreateDebtData;
use App\Modules\Household\Domain\Enums\DebtDirection;
use App\Modules\Household\Domain\Enums\DebtStatus;
use App\Modules\Household\Domain\Enums\InstallmentStatus;
use App\Modules\Household\Domain\Models\Debt;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Facades\DB;

class CreateDebt
{
    public function __construct(
        private readonly NextDocumentNumber $numbers,
        private readonly DebtInstallmentSchedule $schedules,
        private readonly RecordHouseholdPayment $payments,
        private readonly PostJournalEntry $journal,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(CreateDebtData $data): Debt
    {
        if ($data->principalAmount <= 0 || $data->installmentCount < 1 || $data->installmentCount > 120 || $data->annualInterestRate < 0 || trim($data->description) === '') {
            throw new DomainException('La deuda requiere datos positivos, descripción y entre 1 y 120 cuotas.');
        }

        return DB::transaction(function () use ($data): Debt {
            $cashAccount = FinancialAccount::query()->whereIn('scope', [FinancialScope::Household, FinancialScope::Personal])->where('is_active', true)->findOrFail($data->financialAccountId);
            $direction = $data->direction === DebtDirection::Payable ? PaymentDirection::Incoming : PaymentDirection::Outgoing;
            $payment = $this->payments->execute($direction, $data->principalAmount, $cashAccount->id, $data->personId, $data->startDate, $data->description, $data->creator);
            $controlCode = $data->direction === DebtDirection::Payable ? '2105-HOG-DEUDAS' : '1355-HOG-PRESTAMOS';
            $controlAccount = FinancialAccount::query()->where('code', $controlCode)->sole();
            $lines = $data->direction === DebtDirection::Payable
                ? [new JournalLineData($cashAccount->id, $data->principalAmount, 0, $data->personId, 'Dinero recibido'), new JournalLineData($controlAccount->id, 0, $data->principalAmount, $data->personId, 'Obligación adquirida')]
                : [new JournalLineData($controlAccount->id, $data->principalAmount, 0, $data->personId, 'Préstamo entregado'), new JournalLineData($cashAccount->id, 0, $data->principalAmount, $data->personId, 'Dinero entregado')];
            $entry = $this->journal->execute(new JournalEntryData($data->startDate, $data->description, $data->creator, $lines, $payment));
            $debt = Debt::create([
                'document_number' => $this->numbers->execute('household_debt', 'DEU', $data->startDate),
                'person_id' => $data->personId,
                'direction' => $data->direction,
                'description' => trim($data->description),
                'principal_amount' => $data->principalAmount,
                'annual_interest_rate' => $data->annualInterestRate,
                'start_date' => $data->startDate,
                'financial_account_id' => $cashAccount->id,
                'status' => DebtStatus::Active,
                'principal_paid' => 0,
                'interest_paid' => 0,
                'opening_journal_entry_id' => $entry->id,
                'created_by' => $data->creator->id,
            ]);
            foreach ($this->schedules->build($data->principalAmount, $data->annualInterestRate, $data->installmentCount, $data->firstDueAt) as $installment) {
                $debt->installments()->create([...$installment, 'status' => InstallmentStatus::Pending]);
            }
            $payment->allocations()->create(['allocatable_type' => $debt->getMorphClass(), 'allocatable_id' => $debt->id, 'amount' => $payment->amount]);
            $entry->update(['source_type' => $debt->getMorphClass(), 'source_id' => $debt->id]);
            $this->audit->execute('household.debt_created', $debt, $data->creator, after: $debt->fresh('installments')->toArray());

            return $debt->fresh(['installments', 'financialAccount', 'openingJournalEntry']);
        });
    }
}
