<?php

namespace App\Modules\Household\Application;

use App\Models\User;
use App\Modules\CashManagement\Application\FinancialAccountBalance;
use App\Modules\Finance\Domain\Enums\FinancialDocumentStatus;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Enums\PaymentDirection;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\Shared\Application\NextDocumentNumber;
use Carbon\CarbonInterface;
use DomainException;

class RecordHouseholdPayment
{
    public function __construct(
        private readonly NextDocumentNumber $numbers,
        private readonly FinancialAccountBalance $balances,
    ) {}

    public function execute(PaymentDirection $direction, int $amount, string $accountId, ?string $personId, CarbonInterface $paidAt, string $reference, User $actor): Payment
    {
        if ($amount <= 0) {
            throw new DomainException('El pago debe ser positivo.');
        }
        $account = FinancialAccount::query()->whereIn('scope', [FinancialScope::Household, FinancialScope::Personal])->where('accepts_payments', true)->where('is_active', true)->findOrFail($accountId);
        if ($account->scope === FinancialScope::Personal && $account->person_id !== $personId) {
            throw new DomainException('La cuenta personal no pertenece al habitante indicado.');
        }
        if ($direction === PaymentDirection::Outgoing && $this->balances->execute($account->id) < $amount) {
            throw new DomainException('La cuenta seleccionada no tiene saldo suficiente.');
        }

        return Payment::create([
            'document_number' => $this->numbers->execute('household_payment', $direction === PaymentDirection::Incoming ? 'HIN' : 'HEG', $paidAt),
            'direction' => $direction,
            'person_id' => $personId,
            'paid_at' => $paidAt,
            'amount' => $amount,
            'financial_account_id' => $account->id,
            'status' => FinancialDocumentStatus::Confirmed,
            'reference' => trim($reference),
            'created_by' => $actor->id,
        ]);
    }
}
