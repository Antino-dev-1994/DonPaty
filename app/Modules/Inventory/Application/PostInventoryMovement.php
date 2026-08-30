<?php

namespace App\Modules\Inventory\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Identity\Application\UseAuthorization;
use App\Modules\Inventory\Application\Data\InventoryMovementData;
use App\Modules\Inventory\Application\Data\InventoryMovementLineData;
use App\Modules\Inventory\Domain\Enums\InventoryDocumentStatus;
use App\Modules\Inventory\Domain\Models\InventoryBalance;
use App\Modules\Inventory\Domain\Models\InventoryMovement;
use App\Modules\Inventory\Domain\Models\InventoryMovementLine;
use App\Modules\Inventory\Domain\Models\NegativeStockIncident;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Facades\DB;

class PostInventoryMovement
{
    public function __construct(
        private readonly NextDocumentNumber $nextDocumentNumber,
        private readonly UseAuthorization $useAuthorization,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(InventoryMovementData $data): InventoryMovement
    {
        if ($data->lines === []) {
            throw new DomainException('El movimiento debe incluir al menos una línea.');
        }

        return DB::transaction(function () use ($data): InventoryMovement {
            $movement = InventoryMovement::create([
                'document_number' => $this->nextDocumentNumber->execute('inventory_movement', 'INV', $data->effectiveAt),
                'movement_type' => $data->type,
                'effective_at' => $data->effectiveAt,
                'source_type' => $data->source?->getMorphClass(),
                'source_id' => $data->source ? (string) $data->source->getKey() : null,
                'status' => InventoryDocumentStatus::Confirmed,
                'notes' => $data->notes,
                'created_by' => $data->creator->id,
                'authorization_request_id' => $data->authorization?->id,
                'reversal_of_id' => $data->reversalOfId,
            ]);

            $usedAuthorization = false;
            foreach ($data->lines as $lineData) {
                $line = $this->postLine($movement, $lineData, $data);
                $usedAuthorization = $usedAuthorization || bccomp($line->balance_after, '0', 6) < 0;
            }

            if ($usedAuthorization) {
                if (! $data->authorization) {
                    throw new DomainException('La autorización requerida no fue proporcionada.');
                }
                $this->useAuthorization->execute($data->authorization);
            }

            $movement->load('lines');
            $this->audit->execute('inventory.movement_posted', $movement, $data->creator, after: $movement->toArray(), authorizationRequestId: $data->authorization?->id);

            return $movement;
        });
    }

    private function postLine(InventoryMovement $movement, InventoryMovementLineData $data, InventoryMovementData $movementData): InventoryMovementLine
    {
        $this->validateQuantities($data);
        $presentation = ProductPresentation::query()->with('item')->findOrFail($data->presentationId);
        if (! $presentation->is_stockable || ! $presentation->is_active) {
            throw new DomainException('La presentación debe estar activa y ser inventariable.');
        }

        InventoryBalance::query()->firstOrCreate(
            ['presentation_id' => $presentation->id],
            ['physical_quantity' => 0, 'reserved_quantity' => 0, 'average_unit_cost' => 0],
        );
        $balance = InventoryBalance::query()->where('presentation_id', $presentation->id)->lockForUpdate()->sole();
        $before = $balance->physical_quantity;
        $isIncoming = bccomp($data->quantityIn, '0', 6) > 0;
        $quantity = $isIncoming ? $data->quantityIn : $data->quantityOut;
        $after = $isIncoming ? bcadd($before, $quantity, 6) : bcsub($before, $quantity, 6);

        if (bccomp($after, '0', 6) < 0) {
            $this->ensureNegativeStockIsAuthorized($presentation, $movementData);
        }

        $unitCost = $isIncoming
            ? ($data->unitCost ?? $balance->average_unit_cost)
            : $balance->average_unit_cost;
        $totalCost = $data->totalCost ?? (int) round((float) $quantity * $unitCost);
        $averageCost = $isIncoming
            ? $this->weightedAverageCost($before, $balance->average_unit_cost, $quantity, $unitCost, $after)
            : $balance->average_unit_cost;

        $line = $movement->lines()->create([
            'presentation_id' => $presentation->id,
            'quantity_in' => $isIncoming ? $quantity : 0,
            'quantity_out' => $isIncoming ? 0 : $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'balance_before' => $before,
            'balance_after' => $after,
        ]);
        $balance->update([
            'physical_quantity' => $after,
            'average_unit_cost' => $averageCost,
            'updated_at' => now(),
        ]);

        if (! $isIncoming && bccomp($after, '0', 6) < 0) {
            NegativeStockIncident::create([
                'presentation_id' => $presentation->id,
                'inventory_movement_line_id' => $line->id,
                'authorization_request_id' => $movementData->authorization->id,
                'negative_quantity' => ltrim($after, '-'),
                'estimated_unit_cost' => $unitCost,
                'status' => 'pending',
            ]);
        } elseif ($isIncoming && bccomp($after, '0', 6) >= 0) {
            NegativeStockIncident::query()
                ->where('presentation_id', $presentation->id)
                ->where('status', 'pending')
                ->update(['status' => 'regularized', 'regularized_at' => now()]);
        }

        return $line;
    }

    private function validateQuantities(InventoryMovementLineData $data): void
    {
        $hasInput = bccomp($data->quantityIn, '0', 6) > 0;
        $hasOutput = bccomp($data->quantityOut, '0', 6) > 0;
        if ($hasInput === $hasOutput) {
            throw new DomainException('Cada línea debe tener una entrada o una salida positiva, nunca ambas.');
        }
    }

    private function ensureNegativeStockIsAuthorized(ProductPresentation $presentation, InventoryMovementData $data): void
    {
        if (! $presentation->item->allow_negative_stock) {
            throw new DomainException('El artículo no permite inventario negativo.');
        }

        if (! $data->authorization?->isUsable() || $data->authorization->approval_permission !== 'inventory.authorize-negative') {
            throw new DomainException('Se requiere una autorización vigente para continuar con inventario negativo.');
        }
    }

    private function weightedAverageCost(string $currentQuantity, int $currentCost, string $incomingQuantity, int $incomingCost, string $newQuantity): int
    {
        if (bccomp($currentQuantity, '0', 6) <= 0) {
            return $incomingCost;
        }

        if (bccomp($newQuantity, '0', 6) <= 0) {
            return $incomingCost;
        }

        $currentValue = (float) $currentQuantity * $currentCost;
        $incomingValue = (float) $incomingQuantity * $incomingCost;

        return max(0, (int) round(($currentValue + $incomingValue) / (float) $newQuantity));
    }
}
