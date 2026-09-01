<?php

namespace App\Modules\Production\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Catalog\Domain\Services\UnitConverter;
use App\Modules\CostAccounting\Domain\Enums\CostPeriodStatus;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use App\Modules\Production\Application\Data\PlanProductionData;
use App\Modules\Production\Domain\Enums\LaborMethod;
use App\Modules\Production\Domain\Enums\ProductionStatus;
use App\Modules\Production\Domain\Models\ProductionOrder;
use App\Modules\Production\Domain\Services\ConsumablePresentationResolver;
use App\Modules\Recipes\Domain\Models\RecipeCompatibleProduct;
use App\Modules\Recipes\Domain\Models\RecipeVersion;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Facades\DB;

class PlanProduction
{
    public function __construct(private readonly NextDocumentNumber $numbers, private readonly UnitConverter $converter, private readonly ConsumablePresentationResolver $presentations, private readonly RecordAuditEvent $audit) {}

    public function execute(PlanProductionData $data): ProductionOrder
    {
        if (bccomp($data->flourQuantityKg, '0', 6) <= 0 || $data->outputs === []) throw new DomainException('La harina y los productos planeados deben ser positivos.');
        return DB::transaction(function () use ($data): ProductionOrder {
            $version = RecipeVersion::query()->with(['referenceFlourUnit', 'yieldUnit', 'ingredients.item', 'ingredients.unit', 'compatibleProducts.doughWeightUnit', 'compatibleProducts.finishingComponents.item', 'compatibleProducts.finishingComponents.unit'])->applicableOn($data->plannedFor->toDateString())->findOrFail($data->recipeVersionId);
            $period = CostPeriod::query()->where('year', $data->plannedFor->year)->where('month', $data->plannedFor->month)->where('status', CostPeriodStatus::Open)->firstOrFail();
            if ($period->rates()->count() < 2) throw new DomainException('El periodo no tiene tarifas efectivas de gas y electricidad.');
            if ($data->laborMethod === LaborMethod::StandardPerKilogram && $period->standard_labor_rate_per_kg <= 0) throw new DomainException('Configura la tarifa estándar de mano de obra del periodo.');
            $kg = Unit::query()->where('code', 'kg')->sole();
            $referenceKg = $this->converter->convert($version->reference_flour_quantity, $version->referenceFlourUnit, $kg);
            $factor = bcdiv($data->flourQuantityKg, $referenceKg, 8);
            $expectedDoughKg = $this->converter->convert(bcmul($version->expected_dough_yield, $factor, 6), $version->yieldUnit, $kg);
            $order = ProductionOrder::create(['document_number' => $this->numbers->execute('production_order', 'PRO', $data->plannedFor), 'recipe_version_id' => $version->id, 'cost_period_id' => $period->id, 'planned_for' => $data->plannedFor, 'status' => ProductionStatus::Planned, 'flour_quantity' => $data->flourQuantityKg, 'expected_dough_quantity' => $expectedDoughKg, 'labor_method' => $data->laborMethod, 'responsible_person_id' => $data->responsiblePersonId, 'created_by' => $data->creator->id]);
            foreach ($version->ingredients as $ingredient) {
                $presentation = $this->presentations->resolve($ingredient, $ingredient->unit);
                $order->consumptions()->create(['recipe_ingredient_id' => $ingredient->id, 'item_id' => $ingredient->item_id, 'presentation_id' => $presentation->id, 'calculated_quantity' => bcmul($ingredient->quantity, $factor, 6), 'unit_id' => $ingredient->unit_id]);
            }
            $plannedDoughKg = '0';
            foreach ($data->outputs as $outputData) {
                $compatible = RecipeCompatibleProduct::query()->with(['doughWeightUnit', 'finishingComponents.item', 'finishingComponents.unit'])->where('recipe_version_id', $version->id)->findOrFail($outputData->compatibleProductId);
                if (bccomp($outputData->quantity, '0', 6) <= 0) throw new DomainException('Las cantidades de producto deben ser positivas.');
                $unitDoughKg = $this->converter->convert($compatible->dough_weight_per_unit, $compatible->doughWeightUnit, $kg);
                $doughKg = bcmul($unitDoughKg, $outputData->quantity, 6); $plannedDoughKg = bcadd($plannedDoughKg, $doughKg, 6);
                $order->plannedOutputs()->create(['recipe_compatible_product_id' => $compatible->id, 'presentation_id' => $compatible->presentation_id, 'planned_quantity' => $outputData->quantity, 'planned_dough_quantity' => $doughKg]);
                foreach ($compatible->finishingComponents as $component) {
                    $presentation = $this->presentations->resolveForItem($component->item_id, $component->unit, $component->item->name);
                    $order->consumptions()->create(['finishing_component_id' => $component->id, 'item_id' => $component->item_id, 'presentation_id' => $presentation->id, 'calculated_quantity' => bcmul($component->quantity_per_unit, $outputData->quantity, 6), 'unit_id' => $component->unit_id]);
                }
            }
            if (bccomp($plannedDoughKg, $expectedDoughKg, 6) > 0) throw new DomainException('Los productos planeados requieren más masa que el rendimiento esperado.');
            $this->audit->execute('production.order_planned', $order, $data->creator, after: $order->load(['consumptions', 'plannedOutputs'])->toArray());
            return $order;
        });
    }
}
