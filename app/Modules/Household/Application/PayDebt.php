<?php

namespace App\Modules\Household\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Finance\Application\Data\JournalEntryData;
use App\Modules\Finance\Application\Data\JournalLineData;
use App\Modules\Finance\Application\PostJournalEntry;
use App\Modules\Finance\Domain\Enums\PaymentDirection;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Household\Application\Data\PayDebtData;
use App\Modules\Household\Domain\Enums\DebtDirection;
use App\Modules\Household\Domain\Enums\DebtStatus;
use App\Modules\Household\Domain\Enums\InstallmentStatus;
use App\Modules\Household\Domain\Models\Debt;
use App\Modules\Household\Domain\Models\DebtPayment;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Facades\DB;

class PayDebt
{
    public function __construct(
        private readonly DebtPaymentAllocation $allocator,
        private readonly RecordHouseholdPayment $payments,
        private readonly NextDocumentNumber $numbers,
        private readonly PostJournalEntry $journal,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(Debt $debt, PayDebtData $data): DebtPayment
    {
        return DB::transaction(function () use ($debt, $data): DebtPayment {
            $debt = Debt::query()->lockForUpdate()->findOrFail($debt->id);
            if ($debt->status !== DebtStatus::Active) {
                throw new DomainException('Solo se pueden abonar deudas activas.');
            }
            $allocation = $this->allocator->calculate($debt, $data->amount, $data->paidAt);
            $principal = (int) collect($allocation)->sum('principal_amount');
            $interest = (int) collect($allocation)->sum('interest_amount');
            $direction = $debt->direction === DebtDirection::Payable ? PaymentDirection::Outgoing : PaymentDirection::Incoming;
            $payment = $this->payments->execute($direction, $data->amount, $data->financialAccountId, $debt->person_id, $data->paidAt, "Abono {$debt->document_number}", $data->actor);
            $cashAccount = FinancialAccount::query()->findOrFail($data->financialAccountId);
            $principalAccount = FinancialAccount::query()->where('code', $debt->direction === DebtDirection::Payable ? '2105-HOG-DEUDAS' : '1355-HOG-PRESTAMOS')->sole();
            $interestAccount = FinancialAccount::query()->where('code', $debt->direction === DebtDirection::Payable ? '5305-HOG-INTERESES' : '4295-HOG-INTERESES')->sole();
            $lines = $debt->direction === DebtDirection::Payable
                ? [new JournalLineData($principalAccount->id, $principal, 0, $debt->person_id, 'Capital pagado'), new JournalLineData($interestAccount->id, $interest, 0, $debt->person_id, 'Interés pagado'), new JournalLineData($cashAccount->id, 0, $data->amount, $debt->person_id, 'Salida de dinero')]
                : [new JournalLineData($cashAccount->id, $data->amount, 0, $debt->person_id, 'Dinero cobrado'), new JournalLineData($principalAccount->id, 0, $principal, $debt->person_id, 'Capital recuperado'), new JournalLineData($interestAccount->id, 0, $interest, $debt->person_id, 'Interés recibido')];
            $lines = array_values(array_filter($lines, fn (JournalLineData $line) => $line->debit > 0 || $line->credit > 0));
            $entry = $this->journal->execute(new JournalEntryData($data->paidAt, "Abono a {$debt->document_number}", $data->actor, $lines, $payment));
            $debtPayment = DebtPayment::create([
                'document_number' => $this->numbers->execute('debt_payment', 'ABO', $data->paidAt),
                'debt_id' => $debt->id,
                'paid_at' => $data->paidAt,
                'amount' => $data->amount,
                'principal_amount' => $principal,
                'interest_amount' => $interest,
                'financial_account_id' => $cashAccount->id,
                'payment_id' => $payment->id,
                'journal_entry_id' => $entry->id,
                'created_by' => $data->actor->id,
            ]);
            foreach ($allocation as $item) {
                $installment = $item['installment'];
                $installment->increment('principal_paid', $item['principal_amount']);
                $installment->increment('interest_paid', $item['interest_amount']);
                $installment->refresh();
                $isPaid = $installment->principal_paid === $installment->principal_amount && $installment->interest_paid === $installment->interest_amount;
                $hasPayment = $installment->principal_paid > 0 || $installment->interest_paid > 0;
                $installment->update(['status' => $isPaid ? InstallmentStatus::Paid : ($hasPayment ? InstallmentStatus::Partial : InstallmentStatus::Pending)]);
                $debtPayment->applications()->create([
                    'debt_installment_id' => $installment->id,
                    'principal_amount' => $item['principal_amount'],
                    'interest_amount' => $item['interest_amount'],
                ]);
            }
            $debt->increment('principal_paid', $principal);
            $debt->increment('interest_paid', $interest);
            $debt->refresh();
            if (! $debt->installments()->where('status', '!=', InstallmentStatus::Paid->value)->exists()) {
                $debt->update(['status' => DebtStatus::Paid]);
            }
            $payment->allocations()->create(['allocatable_type' => $debtPayment->getMorphClass(), 'allocatable_id' => $debtPayment->id, 'amount' => $payment->amount]);
            $entry->update(['source_type' => $debtPayment->getMorphClass(), 'source_id' => $debtPayment->id]);
            $this->audit->execute('household.debt_payment_posted', $debtPayment, $data->actor, after: $debtPayment->fresh('applications')->toArray());

            return $debtPayment->fresh(['applications', 'journalEntry', 'payment']);
        });
    }
}
