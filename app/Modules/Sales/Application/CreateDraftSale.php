<?php

namespace App\Modules\Sales\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Customers\Domain\Models\CustomerProfile;
use App\Modules\Orders\Domain\Models\SalesOrder;
use App\Modules\Orders\Domain\Models\SalesOrderLine;
use App\Modules\Pricing\Application\ResolvePrice;
use App\Modules\Pricing\Domain\Models\PriceListItem;
use App\Modules\Sales\Application\Data\CreateSaleData;
use App\Modules\Sales\Domain\Enums\SaleStatus;
use App\Modules\Sales\Domain\Models\Sale;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Facades\DB;

class CreateDraftSale
{
    public function __construct(private readonly ResolvePrice $prices, private readonly NextDocumentNumber $numbers, private readonly RecordAuditEvent $audit) {}

    public function execute(CreateSaleData $data): Sale
    {
        return DB::transaction(function () use ($data): Sale {
            if ($data->lines === []) throw new DomainException('La venta debe contener productos.');
            $customer = $data->customerProfileId ? CustomerProfile::query()->where('is_active', true)->findOrFail($data->customerProfileId) : null;
            $order = $data->salesOrderId ? SalesOrder::query()->with('lines')->findOrFail($data->salesOrderId) : null;
            if ($order && $customer && $order->customer_person_id !== $customer->person_id) throw new DomainException('El pedido no pertenece al cliente seleccionado.');
            $sale = Sale::create(['document_number' => $this->numbers->execute('sale','VTA',$data->soldAt), 'customer_person_id' => $customer?->person_id ?? $order?->customer_person_id, 'sales_order_id' => $order?->id, 'sold_at' => $data->soldAt, 'due_at' => $data->dueAt, 'status' => SaleStatus::Draft, 'price_list_id' => $data->priceListId, 'subtotal' => 0, 'discount' => 0, 'total' => 0, 'paid_amount' => 0, 'balance_amount' => 0, 'payment_plan' => array_map(fn ($p) => ['financial_account_id'=>$p->financialAccountId,'amount'=>$p->amount,'reference'=>$p->reference], $data->payments), 'created_by' => $data->actor->id]);
            $subtotal = 0;
            foreach ($data->lines as $line) {
                if (bccomp($line->quantity, '0', 6) <= 0) throw new DomainException('Las cantidades deben ser positivas.');
                $orderLine = $line->salesOrderLineId ? SalesOrderLine::query()->where('sales_order_id', $order?->id)->findOrFail($line->salesOrderLineId) : null;
                if ($orderLine && ($orderLine->presentation_id !== $line->presentationId || bccomp($line->quantity, bcsub($orderLine->ordered_quantity, $orderLine->delivered_quantity, 6), 6) > 0)) throw new DomainException('La entrega supera la cantidad pendiente del pedido.');
                $priceItem = null;
                if ($orderLine) {
                    $listed = $orderLine->list_unit_price; $applied = $orderLine->unit_price; $priceItem = $orderLine->priceListItem;
                } else {
                    $resolved = $this->prices->execute($line->presentationId, $customer, $data->priceListId, $data->soldAt); $priceItem = $resolved['item']; $listed = $priceItem->price; $applied = $line->unitPrice ?? $listed;
                    if ($applied !== $listed && ! $data->actor->hasPermission('prices.override')) throw new DomainException('No tienes permiso para aplicar un precio personalizado.');
                }
                $gross = (int) round((float) $line->quantity * $applied);
                if ($line->discount < 0 || $line->discount > $gross) throw new DomainException('El descuento de línea no es válido.');
                $total = $gross - $line->discount; $subtotal += $total;
                $saleLine = $sale->lines()->create(['sales_order_line_id'=>$orderLine?->id,'presentation_id'=>$line->presentationId,'quantity'=>$line->quantity,'list_unit_price'=>$listed,'applied_unit_price'=>$applied,'discount'=>$line->discount,'line_total'=>$total,'price_reason'=>$line->priceReason]);
                if ($applied !== $listed) {
                    $minimum = (int) max($priceItem?->minimum_price ?? 0, $saleLine->presentation()->value('minimum_sale_price') ?? 0);
                    $saleLine->priceOverride()->create(['presentation_id'=>$line->presentationId,'requested_price'=>$applied,'minimum_price'=>$minimum,'reason'=>$line->priceReason ?: 'Precio personalizado acordado en el pedido.','requested_by'=>$data->actor->id]);
                }
            }
            if ($data->discount < 0 || $data->discount > $subtotal) throw new DomainException('El descuento general no es válido.');
            $total = $subtotal - $data->discount; $planned = array_sum(array_column($sale->payment_plan ?? [], 'amount'));
            if ($total <= 0 || $planned < 0 || $planned > $total) throw new DomainException('El total debe ser positivo y los pagos no pueden superarlo.');
            $sale->update(['subtotal'=>$subtotal,'discount'=>$data->discount,'total'=>$total,'balance_amount'=>$total]);
            $this->audit->execute('sales.draft_created', $sale, $data->actor, after: $sale->fresh(['lines','lines.priceOverride'])->toArray());
            return $sale->fresh(['lines','lines.priceOverride']);
        });
    }
}
