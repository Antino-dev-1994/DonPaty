<?php

namespace App\Modules\Purchasing\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Finance\Application\CreatePayableForPurchase;
use App\Modules\Purchasing\Application\Data\PurchaseData;
use App\Modules\Purchasing\Domain\Enums\PurchaseDocumentStatus;
use App\Modules\Purchasing\Domain\Enums\PurchasePaymentStatus;
use App\Modules\Purchasing\Domain\Enums\PurchaseReceiptStatus;
use App\Modules\Purchasing\Domain\Models\Purchase;
use App\Modules\Purchasing\Domain\Models\SupplierProfile;
use App\Modules\Purchasing\Domain\Services\AdditionalCostAllocator;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Facades\DB;

class CreatePurchase
{
    public function __construct(
        private readonly AdditionalCostAllocator $costAllocator,
        private readonly CreatePayableForPurchase $createPayable,
        private readonly NextDocumentNumber $nextDocumentNumber,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(PurchaseData $data): Purchase
    {
        if ($data->lines === []) {
            throw new DomainException('La compra debe incluir al menos una línea.');
        }
        if (count(array_unique(array_map(fn ($line) => $line->presentationId, $data->lines))) !== count($data->lines)) {
            throw new DomainException('Una presentación no puede repetirse en la compra.');
        }

        return DB::transaction(function () use ($data): Purchase {
            SupplierProfile::query()->where('person_id', $data->supplierPersonId)->where('is_active', true)->firstOrFail();
            $presentationIds = array_map(fn ($line) => $line->presentationId, $data->lines);
            $validCount = ProductPresentation::query()->whereIn('id', $presentationIds)->where('is_active', true)->where('is_purchasable', true)->count();
            if ($validCount !== count($presentationIds)) {
                throw new DomainException('Todas las presentaciones deben estar activas y habilitadas para compra.');
            }

            $lineTotals = array_map(fn ($line) => $line->total(), $data->lines);
            $allocations = $this->costAllocator->allocate($data->additionalCosts, $lineTotals);
            $subtotal = array_sum($lineTotals);
            $total = $subtotal + $data->additionalCosts;
            $purchase = Purchase::create([
                'document_number' => $this->nextDocumentNumber->execute('purchase', 'COM', $data->issuedAt),
                'supplier_person_id' => $data->supplierPersonId, 'supplier_document_number' => $data->supplierDocumentNumber,
                'issued_at' => $data->issuedAt, 'due_at' => $data->dueAt, 'payment_condition' => $data->paymentCondition,
                'status' => PurchaseDocumentStatus::Confirmed, 'receipt_status' => PurchaseReceiptStatus::Pending,
                'payment_status' => PurchasePaymentStatus::Pending, 'subtotal' => $subtotal,
                'additional_costs' => $data->additionalCosts, 'total' => $total,
                'paid_amount' => 0, 'returned_amount' => 0, 'balance_amount' => $total, 'notes' => $data->notes, 'created_by' => $data->creator->id,
            ]);
            foreach ($data->lines as $index => $line) {
                $purchase->lines()->create([
                    'presentation_id' => $line->presentationId, 'ordered_quantity' => $line->quantity,
                    'received_quantity' => 0, 'returned_quantity' => 0, 'unit_price' => $line->unitPrice,
                    'allocated_additional_cost' => $allocations[$index], 'line_total' => $lineTotals[$index],
                ]);
            }
            $this->createPayable->execute($purchase);
            $this->audit->execute('purchasing.purchase_created', $purchase, $data->creator, after: $purchase->load('lines')->toArray());

            return $purchase;
        });
    }
}
