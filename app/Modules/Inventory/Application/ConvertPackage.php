<?php

namespace App\Modules\Inventory\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\Inventory\Application\Data\InventoryMovementData;
use App\Modules\Inventory\Application\Data\InventoryMovementLineData;
use App\Modules\Inventory\Domain\Enums\InventoryDocumentStatus;
use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use App\Modules\Inventory\Domain\Enums\PackageConversionType;
use App\Modules\Inventory\Domain\Models\InventoryBalance;
use App\Modules\Inventory\Domain\Models\PackageConversion;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ConvertPackage
{
    public function __construct(
        private readonly NextDocumentNumber $nextDocumentNumber,
        private readonly PostInventoryMovement $postMovement,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(ProductPresentation $package, PackageConversionType $type, string $quantity, User $actor, ?AuthorizationRequest $authorization = null, ?Carbon $effectiveAt = null): PackageConversion
    {
        if (bccomp($quantity, '0', 6) <= 0) {
            throw new DomainException('La cantidad de paquetes debe ser positiva.');
        }

        return DB::transaction(function () use ($package, $type, $quantity, $actor, $authorization, $effectiveAt): PackageConversion {
            $package->load('packageComponents.componentPresentation');
            if ($package->packageComponents->isEmpty()) {
                throw new DomainException('La presentación no tiene una composición de paquete configurada.');
            }

            $effectiveAt ??= now();
            $conversion = PackageConversion::create([
                'document_number' => $this->nextDocumentNumber->execute('package_conversion', 'PAQ', $effectiveAt),
                'conversion_type' => $type,
                'package_presentation_id' => $package->id,
                'package_quantity' => $quantity,
                'total_cost' => 0,
                'status' => InventoryDocumentStatus::Draft,
                'created_by' => $actor->id,
            ]);

            [$lines, $totalCost] = $type === PackageConversionType::Assembly
                ? $this->assemblyLines($package, $quantity)
                : $this->disassemblyLines($package, $quantity);

            $movement = $this->postMovement->execute(new InventoryMovementData(
                type: $type === PackageConversionType::Assembly ? InventoryMovementType::PackageAssembly : InventoryMovementType::PackageDisassembly,
                effectiveAt: $effectiveAt,
                creator: $actor,
                lines: $lines,
                source: $conversion,
                notes: $type->label().' de '.$package->name,
                authorization: $authorization,
            ));

            $conversion->update([
                'total_cost' => $totalCost,
                'status' => InventoryDocumentStatus::Confirmed,
                'inventory_movement_id' => $movement->id,
            ]);
            $this->audit->execute('inventory.package_converted', $conversion, $actor, after: $conversion->fresh()->toArray(), authorizationRequestId: $authorization?->id);

            return $conversion;
        });
    }

    /** @return array{list<InventoryMovementLineData>, int} */
    private function assemblyLines(ProductPresentation $package, string $quantity): array
    {
        $lines = [];
        $totalCost = 0;
        foreach ($package->packageComponents as $component) {
            $componentQuantity = bcmul($quantity, $component->quantity, 6);
            $balance = $this->balance($component->component_presentation_id);
            $componentCost = (int) round((float) $componentQuantity * $balance->average_unit_cost);
            $totalCost += $componentCost;
            $lines[] = InventoryMovementLineData::outgoing($component->component_presentation_id, $componentQuantity, $componentCost);
        }
        $lines[] = InventoryMovementLineData::incoming($package->id, $quantity, (int) round($totalCost / (float) $quantity), $totalCost);

        return [$lines, $totalCost];
    }

    /** @return array{list<InventoryMovementLineData>, int} */
    private function disassemblyLines(ProductPresentation $package, string $quantity): array
    {
        $packageBalance = $this->balance($package->id);
        $totalCost = (int) round((float) $quantity * $packageBalance->average_unit_cost);
        $lines = [InventoryMovementLineData::outgoing($package->id, $quantity, $totalCost)];
        $weights = $package->packageComponents->map(fn ($component) => (float) $component->quantity * (float) $component->componentPresentation->conversion_to_item_base);
        $totalWeight = $weights->sum();

        $allocatedCost = 0;
        $lastIndex = $package->packageComponents->keys()->last();
        foreach ($package->packageComponents as $index => $component) {
            $componentQuantity = bcmul($quantity, $component->quantity, 6);
            $share = $totalWeight > 0 ? $weights[$index] / $totalWeight : 0;
            $componentTotalCost = $index === $lastIndex ? $totalCost - $allocatedCost : (int) round($totalCost * $share);
            $allocatedCost += $componentTotalCost;
            $componentUnitCost = (int) round($componentTotalCost / (float) $componentQuantity);
            $lines[] = InventoryMovementLineData::incoming($component->component_presentation_id, $componentQuantity, $componentUnitCost, $componentTotalCost);
        }

        return [$lines, $totalCost];
    }

    private function balance(string $presentationId): InventoryBalance
    {
        InventoryBalance::query()->firstOrCreate(
            ['presentation_id' => $presentationId],
            ['physical_quantity' => 0, 'reserved_quantity' => 0, 'average_unit_cost' => 0],
        );

        return InventoryBalance::query()->where('presentation_id', $presentationId)->lockForUpdate()->sole();
    }
}
