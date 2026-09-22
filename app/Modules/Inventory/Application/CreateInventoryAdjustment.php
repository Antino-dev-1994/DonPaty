<?php

namespace App\Modules\Inventory\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Inventory\Application\Data\InventoryAdjustmentData;
use App\Modules\Inventory\Domain\Enums\InventoryDocumentStatus;
use App\Modules\Inventory\Domain\Models\InventoryAdjustment;
use App\Modules\Inventory\Domain\Models\InventoryBalance;
use App\Modules\Inventory\Domain\Models\InventoryMovementLine;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Facades\DB;

class CreateInventoryAdjustment
{
    public function __construct(
        private readonly NextDocumentNumber $nextDocumentNumber,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(InventoryAdjustmentData $data): InventoryAdjustment
    {
        if ($data->lines === []) {
            throw new DomainException('El ajuste debe contener al menos una presentación.');
        }

        return DB::transaction(function () use ($data): InventoryAdjustment {
            $adjustment = InventoryAdjustment::create([
                'document_number' => $this->nextDocumentNumber->execute('inventory_adjustment', 'AJS', $data->effectiveAt),
                'adjustment_type' => $data->type,
                'effective_at' => $data->effectiveAt,
                'status' => InventoryDocumentStatus::Draft,
                'reason' => $data->reason,
                'created_by' => $data->creator->id,
            ]);

            foreach ($data->lines as $input) {
                $presentation = ProductPresentation::query()->findOrFail($input['presentation_id']);
                if (! $presentation->is_stockable || ! $presentation->is_active) {
                    throw new DomainException('Solo se pueden ajustar presentaciones activas e inventariables.');
                }
                if ($data->type === 'initial' && bccomp((string) $input['counted_quantity'], '0', 6) < 0) {
                    throw new DomainException('Un inventario inicial no puede tener cantidades negativas.');
                }

                $balance = InventoryBalance::query()->firstOrCreate(
                    ['presentation_id' => $presentation->id],
                    ['physical_quantity' => 0, 'reserved_quantity' => 0, 'average_unit_cost' => 0],
                );
                if ($data->type === 'initial' && InventoryMovementLine::query()->where('presentation_id', $presentation->id)->exists()) {
                    throw new DomainException("La presentación {$presentation->name} ya tiene movimientos y no admite saldo inicial.");
                }

                $difference = bcsub((string) $input['counted_quantity'], $balance->physical_quantity, 6);
                $unitCost = $input['unit_cost'] ?? $balance->average_unit_cost;
                if (bccomp($difference, '0', 6) > 0 && ($unitCost === null || $unitCost < 0)) {
                    throw new DomainException("Indica un costo para la entrada de {$presentation->name}.");
                }

                $adjustment->lines()->create([
                    'presentation_id' => $presentation->id,
                    'expected_quantity' => $balance->physical_quantity,
                    'counted_quantity' => $input['counted_quantity'],
                    'difference_quantity' => $difference,
                    'unit_cost' => $input['unit_cost'],
                ]);
            }

            $adjustment->load('lines');
            $this->audit->execute('inventory.adjustment_created', $adjustment, $data->creator, after: $adjustment->toArray());

            return $adjustment;
        });
    }
}
