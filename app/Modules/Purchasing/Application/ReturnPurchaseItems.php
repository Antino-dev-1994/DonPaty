<?php

namespace App\Modules\Purchasing\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Finance\Domain\Enums\PayableStatus;
use App\Modules\Inventory\Application\Data\InventoryMovementData;
use App\Modules\Inventory\Application\Data\InventoryMovementLineData;
use App\Modules\Inventory\Application\PostInventoryMovement;
use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use App\Modules\Purchasing\Application\Data\PurchaseReturnData;
use App\Modules\Purchasing\Domain\Enums\PurchaseDocumentStatus;
use App\Modules\Purchasing\Domain\Enums\PurchasePaymentStatus;
use App\Modules\Purchasing\Domain\Enums\PurchasingDocumentStatus;
use App\Modules\Purchasing\Domain\Models\Purchase;
use App\Modules\Purchasing\Domain\Models\PurchaseLine;
use App\Modules\Purchasing\Domain\Models\PurchaseReturn;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Facades\DB;

class ReturnPurchaseItems
{
    public function __construct(
        private readonly NextDocumentNumber $nextDocumentNumber,
        private readonly PostInventoryMovement $postInventoryMovement,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(Purchase $purchase, PurchaseReturnData $data): PurchaseReturn
    {
        if ($data->lines === [] || count(array_unique(array_map(fn ($line) => $line->purchaseLineId, $data->lines))) !== count($data->lines)) {
            throw new DomainException('La devolución debe tener líneas únicas.');
        }

        return DB::transaction(function () use ($purchase, $data): PurchaseReturn {
            $purchase = Purchase::query()->with('payable')->lockForUpdate()->findOrFail($purchase->id);
            if ($purchase->status !== PurchaseDocumentStatus::Confirmed || ! $purchase->payable) {
                throw new DomainException('La compra no está disponible para devolución.');
            }
            $return = PurchaseReturn::create([
                'document_number' => $this->nextDocumentNumber->execute('purchase_return', 'DEV-C', $data->returnedAt),
                'purchase_id' => $purchase->id, 'returned_at' => $data->returnedAt,
                'status' => PurchasingDocumentStatus::Confirmed, 'total_amount' => 0,
                'created_by' => $data->creator->id, 'reason' => $data->reason,
            ]);
            $total = 0; $inventoryLines = [];
            foreach ($data->lines as $lineData) {
                $line = PurchaseLine::query()->where('purchase_id', $purchase->id)->lockForUpdate()->findOrFail($lineData->purchaseLineId);
                $availableToReturn = bcsub($line->received_quantity, $line->returned_quantity, 6);
                if (bccomp($lineData->quantity, '0', 6) <= 0 || bccomp($lineData->quantity, $availableToReturn, 6) > 0) {
                    throw new DomainException('La cantidad devuelta debe ser positiva y no superar lo recibido disponible.');
                }
                $unitCost = $line->unit_price + (int) round($line->allocated_additional_cost / (float) $line->ordered_quantity);
                $lineTotal = (int) round((float) $lineData->quantity * $unitCost);
                $return->lines()->create(['purchase_line_id' => $line->id, 'quantity' => $lineData->quantity, 'unit_cost' => $unitCost, 'total_cost' => $lineTotal]);
                $line->update(['returned_quantity' => bcadd($line->returned_quantity, $lineData->quantity, 6)]);
                $total += $lineTotal;
                $inventoryLines[] = InventoryMovementLineData::outgoing($line->presentation_id, $lineData->quantity);
            }
            if ($total > $purchase->payable->balance_amount) {
                throw new DomainException('Esta devolución supera el saldo por pagar; registra primero el reintegro del proveedor.');
            }

            $movement = $this->postInventoryMovement->execute(new InventoryMovementData(
                type: InventoryMovementType::PurchaseReturn, effectiveAt: $data->returnedAt,
                creator: $data->creator, lines: $inventoryLines, source: $return,
                notes: "Devolución {$return->document_number} de {$purchase->document_number}",
            ));
            $return->update(['total_amount' => $total, 'inventory_movement_id' => $movement->id]);
            $newBalance = $purchase->balance_amount - $total;
            $paymentStatus = $newBalance === 0 ? PurchasePaymentStatus::Paid : ($purchase->paid_amount > 0 ? PurchasePaymentStatus::Partial : PurchasePaymentStatus::Pending);
            $purchase->update(['returned_amount' => $purchase->returned_amount + $total, 'balance_amount' => $newBalance, 'payment_status' => $paymentStatus]);
            $purchase->payable->update([
                'credited_amount' => $purchase->payable->credited_amount + $total,
                'balance_amount' => $newBalance,
                'status' => $newBalance === 0 ? PayableStatus::Paid : ($purchase->paid_amount > 0 ? PayableStatus::Partial : PayableStatus::Pending),
            ]);
            $this->audit->execute('purchasing.purchase_returned', $return, $data->creator, after: $return->load('lines')->toArray());

            return $return->fresh(['lines', 'inventoryMovement']);
        });
    }
}
