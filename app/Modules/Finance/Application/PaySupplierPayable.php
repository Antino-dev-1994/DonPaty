<?php

namespace App\Modules\Finance\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Finance\Application\Data\JournalEntryData;
use App\Modules\Finance\Application\Data\JournalLineData;
use App\Modules\Finance\Application\Data\SupplierPaymentData;
use App\Modules\Finance\Domain\Enums\FinancialDocumentStatus;
use App\Modules\Finance\Domain\Enums\PayableStatus;
use App\Modules\Finance\Domain\Enums\PaymentDirection;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\Payable;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\Purchasing\Domain\Enums\PurchasePaymentStatus;
use App\Modules\Purchasing\Domain\Models\Purchase;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Facades\DB;

class PaySupplierPayable
{
    public function __construct(
        private readonly NextDocumentNumber $nextDocumentNumber,
        private readonly PostJournalEntry $postJournalEntry,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(Payable $payable, SupplierPaymentData $data): Payment
    {
        return DB::transaction(function () use ($payable, $data): Payment {
            $payable = Payable::query()->lockForUpdate()->findOrFail($payable->id);
            if ($data->amount <= 0 || $data->amount > $payable->balance_amount) {
                throw new DomainException('El pago debe ser positivo y no puede superar el saldo pendiente.');
            }
            $cashAccount = FinancialAccount::query()->where('accepts_payments', true)->where('is_active', true)->findOrFail($data->financialAccountId);
            $payablesAccount = FinancialAccount::query()->where('code', '2110-CXP')->where('is_active', true)->sole();

            $payment = Payment::create([
                'document_number' => $this->nextDocumentNumber->execute('payment', 'PAG', $data->paidAt),
                'direction' => PaymentDirection::Outgoing, 'person_id' => $payable->person_id,
                'paid_at' => $data->paidAt, 'amount' => $data->amount, 'financial_account_id' => $cashAccount->id,
                'status' => FinancialDocumentStatus::Confirmed, 'reference' => $data->reference, 'created_by' => $data->creator->id,
            ]);
            $payment->allocations()->create([
                'allocatable_type' => $payable->getMorphClass(), 'allocatable_id' => $payable->id, 'amount' => $data->amount,
            ]);
            $newPaid = $payable->paid_amount + $data->amount;
            $newBalance = $payable->original_amount - $newPaid - $payable->credited_amount;
            $payable->update(['paid_amount' => $newPaid, 'balance_amount' => $newBalance, 'status' => $newBalance === 0 ? PayableStatus::Paid : PayableStatus::Partial]);

            if ($payable->source instanceof Purchase) {
                $payable->source->update([
                    'paid_amount' => $newPaid, 'balance_amount' => $newBalance,
                    'payment_status' => $newBalance === 0 ? PurchasePaymentStatus::Paid : PurchasePaymentStatus::Partial,
                ]);
            }
            $this->postJournalEntry->execute(new JournalEntryData(
                effectiveAt: $data->paidAt, description: "Pago {$payment->document_number} a proveedor",
                poster: $data->creator, source: $payment,
                lines: [
                    new JournalLineData($payablesAccount->id, $data->amount, 0, $payable->person_id, 'Disminución de cuenta por pagar'),
                    new JournalLineData($cashAccount->id, 0, $data->amount, $payable->person_id, 'Salida de dinero'),
                ],
            ));
            $this->audit->execute('finance.supplier_payment_posted', $payment, $data->creator, after: $payment->load('allocations')->toArray());

            return $payment;
        });
    }
}
