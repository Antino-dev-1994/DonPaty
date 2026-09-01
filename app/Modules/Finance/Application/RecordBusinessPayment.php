<?php

namespace App\Modules\Finance\Application;

use App\Models\User;
use App\Modules\CashManagement\Application\FinancialAccountBalance;
use App\Modules\CashManagement\Domain\Enums\CashSessionStatus;
use App\Modules\CashManagement\Domain\Models\CashSession;
use App\Modules\Finance\Domain\Enums\FinancialDocumentStatus;
use App\Modules\Finance\Domain\Enums\PaymentDirection;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\Shared\Application\NextDocumentNumber;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Database\Eloquent\Model;

class RecordBusinessPayment
{
    public function __construct(
        private readonly NextDocumentNumber $numbers,
        private readonly FinancialAccountBalance $balances,
    ) {}

    public function execute(
        PaymentDirection $direction,
        int $amount,
        string $accountId,
        ?string $personId,
        CarbonInterface $paidAt,
        ?string $reference,
        User $actor,
        Model $allocationSource,
    ): Payment {
        if ($amount <= 0) {
            throw new DomainException('El pago debe ser positivo.');
        }

        $account = FinancialAccount::query()
            ->where('accepts_payments', true)
            ->where('is_active', true)
            ->findOrFail($accountId);
        if ($account->code === '1105-CAJA-MENOR' && ! CashSession::query()
            ->where('financial_account_id', $account->id)
            ->where('status', CashSessionStatus::Open)
            ->exists()) {
            throw new DomainException('Debes abrir la caja menor antes de registrar el movimiento.');
        }
        if ($direction === PaymentDirection::Outgoing && $this->balances->execute($account->id) < $amount) {
            throw new DomainException('La cuenta seleccionada no tiene saldo suficiente.');
        }

        $prefix = $direction === PaymentDirection::Incoming ? 'ING' : 'EGR';
        $payment = Payment::create([
            'document_number' => $this->numbers->execute('business_payment', $prefix, $paidAt),
            'direction' => $direction,
            'person_id' => $personId,
            'paid_at' => $paidAt,
            'amount' => $amount,
            'financial_account_id' => $account->id,
            'status' => FinancialDocumentStatus::Confirmed,
            'reference' => $reference,
            'created_by' => $actor->id,
        ]);
        $payment->allocations()->create([
            'allocatable_type' => $allocationSource->getMorphClass(),
            'allocatable_id' => $allocationSource->getKey(),
            'amount' => $amount,
        ]);

        return $payment->load('financialAccount');
    }
}
