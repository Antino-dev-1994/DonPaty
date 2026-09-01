<?php

namespace App\Modules\Purchasing\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Identity\Application\UseAuthorization;
use App\Modules\Inventory\Application\Data\InventoryMovementData;
use App\Modules\Inventory\Application\Data\InventoryMovementLineData;
use App\Modules\Inventory\Application\PostInventoryMovement;
use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use App\Modules\Purchasing\Application\Data\PurchaseReceiptData;
use App\Modules\Purchasing\Domain\Enums\PurchaseDocumentStatus;
use App\Modules\Purchasing\Domain\Enums\PurchaseReceiptStatus;
use App\Modules\Purchasing\Domain\Enums\PurchasingDocumentStatus;
use App\Modules\Purchasing\Domain\Models\Purchase;
use App\Modules\Purchasing\Domain\Models\PurchaseLine;
use App\Modules\Purchasing\Domain\Models\PurchaseReceipt;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Facades\DB;

class ReceivePurchase
{
    public function __construct(
        private readonly NextDocumentNumber $nextDocumentNumber,
        private readonly PostInventoryMovement $postInventoryMovement,
        private readonly UseAuthorization $useAuthorization,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(Purchase $purchase, PurchaseReceiptData $data): PurchaseReceipt
    {
        if ($data->lines === []) {
            throw new DomainException('La recepción debe incluir al menos una línea.');
        }
        if (count(array_unique(array_map(fn ($line) => $line->purchaseLineId, $data->lines))) !== count($data->lines)) {
            throw new DomainException('Una línea de compra no puede repetirse en la recepción.');
        }

        return DB::transaction(function () use ($purchase, $data): PurchaseReceipt {
            $purchase = Purchase::query()->lockForUpdate()->findOrFail($purchase->id);
            if ($purchase->status !== PurchaseDocumentStatus::Confirmed) {
                throw new DomainException('Solo se pueden recibir compras confirmadas.');
            }

            $receipt = PurchaseReceipt::create([
                'document_number' => $this->nextDocumentNumber->execute('purchase_receipt', 'REC', $data->receivedAt),
                'purchase_id' => $purchase->id, 'received_at' => $data->receivedAt,
                'status' => PurchasingDocumentStatus::Confirmed, 'received_by' => $data->receiver->id, 'notes' => $data->notes,
            ]);
            $inventoryLines = [];
            $requiresAuthorization = false;

            foreach ($data->lines as $lineData) {
                $line = PurchaseLine::query()->where('purchase_id', $purchase->id)->lockForUpdate()->findOrFail($lineData->purchaseLineId);
                if (bccomp($lineData->quantity, '0', 6) <= 0) {
                    throw new DomainException('Todas las cantidades recibidas deben ser positivas.');
                }
                $newReceived = bcadd($line->received_quantity, $lineData->quantity, 6);
                if (bccomp($newReceived, $line->ordered_quantity, 6) > 0) {
                    $requiresAuthorization = true;
                }

                $additionalUnitCost = (int) round($line->allocated_additional_cost / (float) $line->ordered_quantity);
                $unitCost = $line->unit_price + $additionalUnitCost;
                $totalCost = (int) round((float) $lineData->quantity * $unitCost);
                $receipt->lines()->create([
                    'purchase_line_id' => $line->id, 'quantity' => $lineData->quantity,
                    'unit_cost' => $unitCost, 'total_cost' => $totalCost,
                ]);
                $line->update(['received_quantity' => $newReceived]);
                $inventoryLines[] = InventoryMovementLineData::incoming($line->presentation_id, $lineData->quantity, $unitCost, $totalCost);
            }

            if ($requiresAuthorization) {
                if (! $data->authorization?->isUsable() || $data->authorization->approval_permission !== 'purchases.authorize-over-receipt') {
                    throw new DomainException('Recibir más de lo comprado requiere una autorización vigente.');
                }
                $this->useAuthorization->execute($data->authorization);
            }

            $movement = $this->postInventoryMovement->execute(new InventoryMovementData(
                type: InventoryMovementType::PurchaseReceipt, effectiveAt: $data->receivedAt,
                creator: $data->receiver, lines: $inventoryLines, source: $receipt,
                notes: "Recepción {$receipt->document_number} de {$purchase->document_number}",
            ));
            $receipt->update(['inventory_movement_id' => $movement->id]);

            $hasPending = PurchaseLine::query()->where('purchase_id', $purchase->id)
                ->whereColumn('received_quantity', '<', 'ordered_quantity')->exists();
            $purchase->update(['receipt_status' => $hasPending ? PurchaseReceiptStatus::Partial : PurchaseReceiptStatus::Received]);
            $this->audit->execute('purchasing.purchase_received', $receipt, $data->receiver, after: $receipt->load('lines')->toArray(), authorizationRequestId: $data->authorization?->id);

            return $receipt->fresh(['lines', 'inventoryMovement']);
        });
    }
}
