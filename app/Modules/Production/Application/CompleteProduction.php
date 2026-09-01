<?php

namespace App\Modules\Production\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Catalog\Domain\Services\UnitConverter;
use App\Modules\CostAccounting\Domain\Enums\CostPeriodStatus;
use App\Modules\CostAccounting\Domain\Models\CostAllocation;
use App\Modules\Inventory\Application\Data\InventoryMovementData;
use App\Modules\Inventory\Application\Data\InventoryMovementLineData;
use App\Modules\Inventory\Application\PostInventoryMovement;
use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use App\Modules\Production\Application\Data\CompleteProductionData;
use App\Modules\Production\Domain\Enums\ProductionStatus;
use App\Modules\Production\Domain\Models\ProductionConsumption;
use App\Modules\Production\Domain\Models\ProductionOrder;
use App\Modules\Production\Domain\Services\IntegerCostAllocator;
use App\Modules\Recipes\Domain\Models\RecipeCompatibleProduct;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CompleteProduction
{
    public function __construct(private readonly UnitConverter $converter, private readonly PostInventoryMovement $postMovement, private readonly CalculateProductionLabor $labor, private readonly IntegerCostAllocator $allocator, private readonly RecordAuditEvent $audit) {}

    public function execute(ProductionOrder $order, CompleteProductionData $data): ProductionOrder
    {
        return DB::transaction(function () use ($order, $data): ProductionOrder {
            $order = ProductionOrder::query()->with(['costPeriod.rates', 'consumptions.unit', 'consumptions.presentation.stockUnit'])->lockForUpdate()->findOrFail($order->id);
            if ($order->status !== ProductionStatus::InProgress || $order->costPeriod->status !== CostPeriodStatus::Open) throw new DomainException('La producción y su periodo deben estar abiertos.');
            if ($order->started_at && $data->completedAt->lt($order->started_at)) throw new DomainException('La finalización no puede ser anterior al inicio.');
            if (bccomp($data->actualDoughQuantityKg, '0', 6) <= 0 || bccomp($data->wasteQuantityKg, '0', 6) < 0) throw new DomainException('La masa real debe ser positiva y la merma no puede ser negativa.');

            [$inventoryLines, $stockQuantities, $consumptionRecords] = $this->prepareConsumptions($order, $data);
            $consumptionMovement = $this->postMovement->execute(new InventoryMovementData(
                type: InventoryMovementType::ProductionConsumption, effectiveAt: $data->completedAt, creator: $data->actor,
                lines: $inventoryLines, source: $order, notes: "Consumos de {$order->document_number}", authorization: $order->negativeStockAuthorization,
            ));
            $movementByPresentation = $consumptionMovement->lines->keyBy('presentation_id');
            foreach ($consumptionRecords as $record) {
                if (bccomp($stockQuantities[$record->id], '0', 6) === 0) {
                    $record->update(['unit_cost' => 0, 'total_cost' => 0]);
                    continue;
                }
                $movementLine = $movementByPresentation->get($record->presentation_id);
                $record->update(['unit_cost' => $movementLine->unit_cost, 'total_cost' => (int) round((float) $stockQuantities[$record->id] * $movementLine->unit_cost)]);
            }
            $ingredientCost = (int) $consumptionMovement->lines->sum('total_cost');
            $laborCost = $this->labor->execute($order, $data);
            CostAllocation::create(['cost_period_id' => $order->cost_period_id, 'production_order_id' => $order->id, 'cost_type' => 'labor', 'base_quantity' => $order->flour_quantity, 'rate' => (int) round($laborCost / (float) $order->flour_quantity), 'amount' => $laborCost]);
            $overheadCost = $this->allocateOverheads($order);
            $totalCost = $ingredientCost + $laborCost + $overheadCost;

            [$outputRecords, $outputLines] = $this->prepareOutputs($order, $data, $totalCost);
            $outputMovement = $this->postMovement->execute(new InventoryMovementData(
                type: InventoryMovementType::ProductionOutput, effectiveAt: $data->completedAt, creator: $data->actor,
                lines: $outputLines, source: $order, notes: "Productos obtenidos en {$order->document_number}",
            ));
            foreach ($outputRecords as $record) $order->outputs()->create($record);
            $order->costPeriod->increment('processed_flour_quantity', (float) $order->flour_quantity);
            $order->update(['status' => ProductionStatus::Completed, 'completed_at' => $data->completedAt, 'actual_dough_quantity' => $data->actualDoughQuantityKg, 'waste_quantity' => $data->wasteQuantityKg, 'consumption_movement_id' => $consumptionMovement->id, 'output_movement_id' => $outputMovement->id, 'ingredient_cost' => $ingredientCost, 'labor_cost' => $laborCost, 'overhead_cost' => $overheadCost, 'total_cost' => $totalCost]);
            if (bccomp($data->actualDoughQuantityKg, $order->expected_dough_quantity, 3) !== 0) $order->incidents()->create(['incident_type' => 'yield_difference', 'description' => 'La masa real difiere del rendimiento esperado.', 'quantity' => bcsub($data->actualDoughQuantityKg, $order->expected_dough_quantity, 6), 'recorded_by' => $data->actor->id, 'recorded_at' => $data->completedAt]);
            $this->audit->execute('production.order_completed', $order, $data->actor, after: $order->fresh(['outputs', 'consumptions', 'laborEntries', 'overheadAllocations'])->toArray(), authorizationRequestId: $order->negative_stock_authorization_id);
            return $order->fresh(['outputs', 'consumptions', 'laborEntries', 'overheadAllocations']);
        });
    }

    /** @return array{list<InventoryMovementLineData>,array<string,string>,Collection<int,ProductionConsumption>} */
    private function prepareConsumptions(ProductionOrder $order, CompleteProductionData $data): array
    {
        if (count($data->consumptions) !== $order->consumptions->count() || count(array_unique(array_map(fn($line)=>$line->consumptionId,$data->consumptions))) !== count($data->consumptions)) throw new DomainException('Debes registrar el consumo real de cada ingrediente.');
        $groups = []; $stockQuantities = []; $records = collect();
        foreach ($data->consumptions as $actual) {
            $record = $order->consumptions->firstWhere('id', $actual->consumptionId) ?? throw new DomainException('El consumo no pertenece a esta producción.');
            if (bccomp($actual->actualQuantity, '0', 6) < 0) throw new DomainException('El consumo real no puede ser negativo.');
            $different = bccomp($actual->actualQuantity, $record->calculated_quantity, 6) !== 0;
            if ($different && trim((string)$actual->differenceReason) === '') throw new DomainException('Cada diferencia de consumo requiere una novedad o motivo.');
            $record->update(['actual_quantity' => $actual->actualQuantity, 'difference_reason' => $actual->differenceReason]);
            if ($different) $order->incidents()->create(['incident_type' => 'consumption_difference', 'description' => $actual->differenceReason, 'quantity' => bcsub($actual->actualQuantity, $record->calculated_quantity, 6), 'recorded_by' => $data->actor->id, 'recorded_at' => $data->completedAt]);
            $stockQuantity = $this->converter->convert($actual->actualQuantity, $record->unit, $record->presentation->stockUnit); $stockQuantities[$record->id] = $stockQuantity;
            $groups[$record->presentation_id] = bcadd($groups[$record->presentation_id] ?? '0', $stockQuantity, 6); $records->push($record);
        }
        $lines = [];
        foreach ($groups as $presentationId => $quantity) if (bccomp($quantity, '0', 6) > 0) $lines[] = InventoryMovementLineData::outgoing($presentationId, $quantity);
        if ($lines === []) throw new DomainException('La producción debe consumir al menos un ingrediente.');
        return [$lines, $stockQuantities, $records];
    }

    private function allocateOverheads(ProductionOrder $order): int
    {
        $total = 0;
        foreach ($order->costPeriod->rates as $rate) {
            $amount = (int) round((float) $order->flour_quantity * $rate->effective_rate); $total += $amount;
            $order->overheadAllocations()->create(['overhead_rate_id' => $rate->id, 'cost_type' => $rate->cost_type, 'base_quantity' => $order->flour_quantity, 'rate' => $rate->effective_rate, 'amount' => $amount]);
            CostAllocation::create(['cost_period_id' => $order->cost_period_id, 'production_order_id' => $order->id, 'cost_type' => $rate->cost_type->value, 'base_quantity' => $order->flour_quantity, 'rate' => $rate->effective_rate, 'amount' => $amount]);
        }
        return $total;
    }

    /** @return array{list<array<string,mixed>>,list<InventoryMovementLineData>} */
    private function prepareOutputs(ProductionOrder $order, CompleteProductionData $data, int $totalCost): array
    {
        if ($data->outputs === [] || count(array_unique(array_map(fn($line)=>$line->compatibleProductId,$data->outputs))) !== count($data->outputs)) throw new DomainException('Registra productos obtenidos únicos.');
        $doughTotal = $data->wasteQuantityKg; $products = []; $weights = [];
        foreach ($data->outputs as $line) {
            $compatible = RecipeCompatibleProduct::query()->where('recipe_version_id', $order->recipe_version_id)->findOrFail($line->compatibleProductId);
            if (bccomp($line->quantity, '0', 6) <= 0 || bccomp($line->doughQuantityKg, '0', 6) <= 0) throw new DomainException('Productos y masa distribuida deben ser positivos.');
            $doughTotal = bcadd($doughTotal, $line->doughQuantityKg, 6); $products[] = [$line, $compatible]; $weights[] = (float)$line->doughQuantityKg * (float)$compatible->cost_weight_factor;
            if (! $order->plannedOutputs()->where('recipe_compatible_product_id', $compatible->id)->exists()) $order->incidents()->create(['incident_type' => 'output_change', 'description' => 'Se obtuvo un producto compatible que no estaba en la planeación inicial.', 'quantity' => $line->quantity, 'recorded_by' => $data->actor->id, 'recorded_at' => $data->completedAt]);
        }
        if (bccomp($doughTotal, $data->actualDoughQuantityKg, 3) !== 0) throw new DomainException('La masa distribuida más la merma debe coincidir con la masa real.');
        $allocations = $this->allocator->allocate($totalCost, $weights); $records = []; $inventoryLines = [];
        foreach ($products as $index => [$line, $compatible]) {
            $allocated = $allocations[$index]; $unitCost = (int) round($allocated / (float)$line->quantity);
            $records[] = ['recipe_compatible_product_id' => $compatible->id, 'presentation_id' => $compatible->presentation_id, 'quantity' => $line->quantity, 'dough_quantity' => $line->doughQuantityKg, 'waste_quantity' => 0, 'allocated_cost' => $allocated, 'unit_cost' => $unitCost];
            $inventoryLines[] = InventoryMovementLineData::incoming($compatible->presentation_id, $line->quantity, $unitCost, $allocated);
        }
        return [$records, $inventoryLines];
    }
}
