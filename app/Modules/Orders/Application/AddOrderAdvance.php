<?php

namespace App\Modules\Orders\Application;

use App\Models\User;
use App\Modules\Finance\Application\Data\JournalEntryData;
use App\Modules\Finance\Application\Data\JournalLineData;
use App\Modules\Finance\Application\PostJournalEntry;
use App\Modules\Finance\Domain\Enums\FinancialDocumentStatus;
use App\Modules\Finance\Domain\Enums\PaymentDirection;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\Orders\Domain\Enums\SalesOrderStatus;
use App\Modules\Orders\Domain\Models\SalesOrder;
use App\Modules\Shared\Application\NextDocumentNumber;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

class AddOrderAdvance
{
    public function __construct(private readonly NextDocumentNumber $numbers, private readonly PostJournalEntry $journal) {}

    public function execute(SalesOrder $order, int $amount, string $accountId, CarbonInterface $paidAt, User $actor, ?string $reference = null): Payment
    {
        return DB::transaction(function () use ($order, $amount, $accountId, $paidAt, $actor, $reference): Payment {
            $order = SalesOrder::query()->lockForUpdate()->findOrFail($order->id);
            if (in_array($order->status, [SalesOrderStatus::Delivered, SalesOrderStatus::Cancelled], true)) throw new DomainException('El pedido no admite anticipos.');
            if ($amount <= 0 || $amount > $order->balance_amount) throw new DomainException('El anticipo debe ser positivo y no superar el saldo del pedido.');
            $cash = FinancialAccount::query()->where('accepts_payments', true)->where('is_active', true)->findOrFail($accountId);
            $liability = FinancialAccount::query()->where('code', '2805-ANTICIPOS')->where('is_active', true)->sole();
            $payment = Payment::create(['document_number' => $this->numbers->execute('customer_advance','ANT',$paidAt), 'direction' => PaymentDirection::Incoming, 'person_id' => $order->customer_person_id, 'paid_at' => $paidAt, 'amount' => $amount, 'financial_account_id' => $cash->id, 'status' => FinancialDocumentStatus::Confirmed, 'reference' => $reference, 'created_by' => $actor->id]);
            $payment->allocations()->create(['allocatable_type' => $order->getMorphClass(), 'allocatable_id' => $order->id, 'amount' => $amount]);
            $this->journal->execute(new JournalEntryData($paidAt, "Anticipo {$order->document_number}", $actor, [new JournalLineData($cash->id,$amount,0,$order->customer_person_id), new JournalLineData($liability->id,0,$amount,$order->customer_person_id)], $payment));
            $order->update(['advance_amount' => $order->advance_amount + $amount, 'balance_amount' => $order->balance_amount - $amount]);
            return $payment->load('allocations');
        });
    }
}
