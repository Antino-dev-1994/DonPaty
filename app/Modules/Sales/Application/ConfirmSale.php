<?php

namespace App\Modules\Sales\Application;

use App\Models\User;
use App\Modules\CashManagement\Domain\Enums\CashSessionStatus;
use App\Modules\CashManagement\Domain\Models\CashSession;
use App\Modules\Customers\Domain\Models\CustomerProfile;
use App\Modules\Finance\Application\Data\JournalEntryData;
use App\Modules\Finance\Application\Data\JournalLineData;
use App\Modules\Finance\Application\PostJournalEntry;
use App\Modules\Finance\Domain\Enums\FinancialDocumentStatus;
use App\Modules\Finance\Domain\Enums\PaymentDirection;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\Finance\Domain\Models\PaymentAllocation;
use App\Modules\Identity\Application\UseAuthorization;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\Inventory\Application\Data\InventoryMovementData;
use App\Modules\Inventory\Application\Data\InventoryMovementLineData;
use App\Modules\Inventory\Application\PostInventoryMovement;
use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use App\Modules\Inventory\Domain\Models\InventoryBalance;
use App\Modules\Orders\Domain\Enums\ReservationStatus;
use App\Modules\Orders\Domain\Enums\SalesOrderStatus;
use App\Modules\Orders\Domain\Models\InventoryReservation;
use App\Modules\Sales\Domain\Enums\ReceivableStatus;
use App\Modules\Sales\Domain\Enums\SaleStatus;
use App\Modules\Sales\Domain\Models\Receivable;
use App\Modules\Sales\Domain\Models\Sale;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Facades\DB;

class ConfirmSale
{
    public function __construct(private readonly EnsureSaleInventory $inventory, private readonly PostInventoryMovement $postMovement, private readonly PostJournalEntry $journal, private readonly NextDocumentNumber $numbers, private readonly UseAuthorization $useAuthorization) {}

    public function execute(Sale $sale, User $actor, ?AuthorizationRequest $priceAuthorization = null, ?AuthorizationRequest $inventoryAuthorization = null, ?AuthorizationRequest $creditAuthorization = null): Sale
    {
        return DB::transaction(function () use ($sale, $actor, $priceAuthorization, $inventoryAuthorization, $creditAuthorization): Sale {
            $sale = Sale::query()->with(['lines.presentation', 'lines.priceOverride', 'lines.orderLine.reservations', 'order.lines'])->lockForUpdate()->findOrFail($sale->id);
            if ($sale->status !== SaleStatus::Draft || $sale->total <= 0) throw new DomainException('Solo puede confirmarse una venta en borrador con total positivo.');
            $belowMinimum = $sale->lines->contains(fn ($line) => $line->priceOverride && $line->applied_unit_price < $line->priceOverride->minimum_price);
            if ($belowMinimum) $this->ensureAuthorization($priceAuthorization, $sale, 'prices.authorize-below-minimum', 'El precio bajo el mínimo requiere autorización.');
            if ($inventoryAuthorization) $this->ensureAuthorization($inventoryAuthorization, $sale, 'inventory.authorize-negative', 'La autorización de inventario no corresponde a esta venta.');

            $inventoryLines = [];
            foreach ($sale->lines as $line) {
                $ownReserved = $line->orderLine ? (string) min((float) $line->quantity, (float) $line->orderLine->reserved_quantity) : '0';
                $this->inventory->execute($line->presentation, $line->quantity, $ownReserved, $actor, $inventoryAuthorization);
                if (bccomp($ownReserved, '0', 6) > 0) $this->consumeReservation($line->orderLine, $ownReserved);
                $inventoryLines[] = InventoryMovementLineData::outgoing($line->presentation_id, $line->quantity);
            }
            $movement = $this->postMovement->execute(new InventoryMovementData(InventoryMovementType::Sale, $sale->sold_at, $actor, $inventoryLines, $sale, "Venta {$sale->document_number}", $inventoryAuthorization));
            $movementByPresentation = $movement->lines->keyBy('presentation_id'); $cogs = 0;
            foreach ($sale->lines as $line) { $movementLine = $movementByPresentation[$line->presentation_id]; $line->update(['unit_cost' => $movementLine->unit_cost, 'total_cost' => $movementLine->total_cost]); $cogs += $movementLine->total_cost; if ($line->priceOverride) $line->priceOverride->update(['authorization_request_id' => $belowMinimum ? $priceAuthorization?->id : null]); }

            $advanceAvailable = 0;
            if ($sale->order) { $used = (int) Sale::query()->where('sales_order_id', $sale->order->id)->where('id', '!=', $sale->id)->whereNot('status', SaleStatus::Reversed)->sum('advance_applied'); $advanceAvailable = max(0, $sale->order->advance_amount - $used); }
            $advanceApplied = min($sale->total, $advanceAvailable); $plannedPayments = collect($sale->payment_plan ?? []); $cashPaid = (int) $plannedPayments->sum('amount');
            if ($cashPaid < 0 || $advanceApplied + $cashPaid > $sale->total) throw new DomainException('Los pagos y anticipos superan el total de la venta.');
            $balance = $sale->total - $advanceApplied - $cashPaid;
            if ($balance > 0 && ! $sale->customer_person_id) throw new DomainException('Una venta a crédito requiere cliente.');
            if ($balance > 0) $this->ensureCreditLimit($sale, $balance, $creditAuthorization);

            $journalLines = []; $payments = [];
            foreach ($plannedPayments as $plan) {
                $amount = (int) $plan['amount']; if ($amount <= 0) continue;
                $account = FinancialAccount::query()->where('accepts_payments', true)->where('is_active', true)->findOrFail($plan['financial_account_id']);
                if ($account->code === '1105-CAJA-MENOR' && ! CashSession::query()->where('financial_account_id', $account->id)->where('status', CashSessionStatus::Open)->exists()) throw new DomainException('Debes abrir la caja menor antes de recibir efectivo.');
                $payment = Payment::create(['document_number' => $this->numbers->execute('sale_payment','RCB',$sale->sold_at), 'direction' => PaymentDirection::Incoming, 'person_id' => $sale->customer_person_id, 'paid_at' => $sale->sold_at, 'amount' => $amount, 'financial_account_id' => $account->id, 'status' => FinancialDocumentStatus::Confirmed, 'reference' => $plan['reference'] ?? null, 'created_by' => $actor->id]);
                $payment->allocations()->create(['allocatable_type' => $sale->getMorphClass(), 'allocatable_id' => $sale->id, 'amount' => $amount]); $payments[] = $payment; $journalLines[] = new JournalLineData($account->id, $amount, 0, $sale->customer_person_id, 'Pago recibido');
            }
            if ($advanceApplied > 0) $journalLines[] = new JournalLineData(FinancialAccount::query()->where('code','2805-ANTICIPOS')->sole()->id, $advanceApplied, 0, $sale->customer_person_id, 'Aplicación de anticipo');
            if ($balance > 0) $journalLines[] = new JournalLineData(FinancialAccount::query()->where('code','1305-CXC')->sole()->id, $balance, 0, $sale->customer_person_id, 'Cuenta por cobrar');
            $journalLines[] = new JournalLineData(FinancialAccount::query()->where('code','4135-VENTAS')->sole()->id, 0, $sale->total, $sale->customer_person_id, 'Ingreso por venta');
            if ($cogs > 0) { $journalLines[] = new JournalLineData(FinancialAccount::query()->where('code','6135-COSTO-VENTAS')->sole()->id, $cogs, 0, $sale->customer_person_id, 'Costo de venta'); $journalLines[] = new JournalLineData(FinancialAccount::query()->where('code','1435-INVENTARIO')->sole()->id, 0, $cogs, $sale->customer_person_id, 'Salida de inventario'); }
            $entry = $this->journal->execute(new JournalEntryData($sale->sold_at, "Venta {$sale->document_number}", $actor, $journalLines, $sale));
            if ($balance > 0) Receivable::create(['document_number' => $this->numbers->execute('receivable','CXC',$sale->sold_at), 'person_id' => $sale->customer_person_id, 'source_type' => $sale->getMorphClass(), 'source_id' => $sale->id, 'issued_at' => $sale->sold_at, 'due_at' => $sale->due_at, 'original_amount' => $balance, 'balance_amount' => $balance, 'status' => ReceivableStatus::Pending]);
            $status = $balance === 0 ? SaleStatus::Paid : (($advanceApplied + $cashPaid) > 0 ? SaleStatus::PartiallyPaid : SaleStatus::Confirmed);
            $sale->update(['status' => $status, 'advance_applied' => $advanceApplied, 'paid_amount' => $advanceApplied + $cashPaid, 'balance_amount' => $balance, 'cost_of_goods_sold' => $cogs, 'gross_profit' => $sale->total - $cogs, 'inventory_movement_id' => $movement->id, 'journal_entry_id' => $entry->id, 'price_authorization_id' => $belowMinimum ? $priceAuthorization?->id : null, 'inventory_authorization_id' => $inventoryAuthorization?->id]);
            if ($belowMinimum) $this->useAuthorization->execute($priceAuthorization);
            $this->updateOrder($sale, $actor);
            return $sale->fresh(['lines','receivable']);
        });
    }

    private function ensureAuthorization(?AuthorizationRequest $authorization, Sale $sale, string $permission, string $message): void { if (! $authorization?->isUsable() || $authorization->approval_permission !== $permission || $authorization->resource_type !== $sale->getMorphClass() || (string) $authorization->resource_id !== (string) $sale->id) throw new DomainException($message); }
    private function ensureCreditLimit(Sale $sale, int $newBalance, ?AuthorizationRequest $authorization): void { $profile=CustomerProfile::query()->where('person_id',$sale->customer_person_id)->sole();$open=(int)Receivable::query()->where('person_id',$sale->customer_person_id)->whereIn('status',[ReceivableStatus::Pending,ReceivableStatus::Partial])->sum('balance_amount');if($open+$newBalance>$profile->credit_limit){$this->ensureAuthorization($authorization,$sale,'sales.authorize-credit','El saldo supera el límite de crédito del cliente.');$this->useAuthorization->execute($authorization);} }
    private function consumeReservation($line,string $quantity):void{$remaining=$quantity;foreach($line->reservations->where('status',ReservationStatus::Active) as $reservation){if(bccomp((string)$remaining,'0',6)<=0)break;$used=(string)min((float)$remaining,(float)$reservation->quantity);$left=bcsub($reservation->quantity,$used,6);if(bccomp($left,'0',6)===0)$reservation->update(['status'=>ReservationStatus::Consumed,'released_at'=>now()]);else{$reservation->update(['quantity'=>$left]);InventoryReservation::create(['presentation_id'=>$reservation->presentation_id,'sales_order_line_id'=>$line->id,'production_order_allocation_id'=>$reservation->production_order_allocation_id,'quantity'=>$used,'status'=>ReservationStatus::Consumed,'reserved_at'=>$reservation->reserved_at,'released_at'=>now()]);}$remaining=bcsub((string)$remaining,$used,6);}$balance=InventoryBalance::query()->where('presentation_id',$line->presentation_id)->lockForUpdate()->sole();$balance->update(['reserved_quantity'=>max(0,(float)$balance->reserved_quantity-(float)$quantity)]);$line->update(['reserved_quantity'=>max(0,(float)$line->reserved_quantity-(float)$quantity)]);}
    private function updateOrder(Sale $sale,User $actor):void{if(!$sale->order)return;foreach($sale->lines->whereNotNull('sales_order_line_id') as $saleLine){$line=$saleLine->orderLine;$line->update(['delivered_quantity'=>bcadd($line->delivered_quantity,$saleLine->quantity,6)]);}$order=$sale->order->fresh('lines');$delivered=$order->lines->every(fn($line)=>bccomp($line->delivered_quantity,$line->ordered_quantity,6)>=0);if($delivered){$from=$order->status;$order->update(['status'=>SalesOrderStatus::Delivered]);$order->histories()->create(['from_status'=>$from->value,'to_status'=>SalesOrderStatus::Delivered->value,'changed_by'=>$actor->id,'changed_at'=>now(),'notes'=>"Entregado mediante {$sale->document_number}."]);}}
}
