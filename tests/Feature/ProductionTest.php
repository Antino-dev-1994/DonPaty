<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Application\EnsureCatalogReferenceData;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\CostAccounting\Application\Data\OpenCostPeriodData;
use App\Modules\CostAccounting\Application\Data\UtilityCostData;
use App\Modules\CostAccounting\Application\OpenCostPeriod;
use App\Modules\CostAccounting\Domain\Enums\UtilityType;
use App\Modules\CostAccounting\Domain\Models\CostAllocation;
use App\Modules\Identity\Application\EnsureAccessControlCatalog;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Inventory\Application\ConfirmInventoryAdjustment;
use App\Modules\Inventory\Application\CreateInventoryAdjustment;
use App\Modules\Inventory\Application\Data\InventoryAdjustmentData;
use App\Modules\Inventory\Domain\Models\InventoryBalance;
use App\Modules\People\Domain\Models\Person;
use App\Modules\Production\Application\CompleteProduction;
use App\Modules\Production\Application\Data\ActualConsumptionData;
use App\Modules\Production\Application\Data\ActualOutputData;
use App\Modules\Production\Application\Data\CompleteProductionData;
use App\Modules\Production\Application\Data\PlanProductionData;
use App\Modules\Production\Application\Data\PlannedOutputData;
use App\Modules\Production\Application\PlanProduction;
use App\Modules\Production\Application\ReverseProduction;
use App\Modules\Production\Application\StartProduction;
use App\Modules\Production\Domain\Enums\LaborMethod;
use App\Modules\Production\Domain\Models\ProductionOrder;
use App\Modules\Recipes\Application\CreateRecipe;
use App\Modules\Recipes\Application\Data\CompatibleProductData;
use App\Modules\Recipes\Application\Data\RecipeData;
use App\Modules\Recipes\Application\Data\RecipeIngredientData;
use App\Modules\Recipes\Application\Data\RecipeVersionData;
use App\Modules\Recipes\Application\PublishRecipeVersion;
use App\Modules\Recipes\Domain\Enums\IngredientRole;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_posts_real_consumptions_two_outputs_costs_and_reversal_atomically(): void
    {
        [$owner, $person, $version, $flour, $yeast, $smallBread, $largeBread] = $this->context(withStock: true);
        $order = $this->plan($owner, $person, $version, $smallBread, $largeBread);
        $smallCompatible = $version->compatibleProducts()->where('presentation_id', $smallBread->id)->sole();
        $largeCompatible = $version->compatibleProducts()->where('presentation_id', $largeBread->id)->sole();

        app(StartProduction::class)->execute($order, $owner);
        $order->refresh()->load('consumptions');
        $completed = app(CompleteProduction::class)->execute($order, new CompleteProductionData(
            Carbon::parse('2026-09-15 10:00'),
            '1.6',
            '0',
            $owner,
            $order->consumptions->map(fn ($line) => new ActualConsumptionData($line->id, $line->calculated_quantity, null))->all(),
            [new ActualOutputData($smallCompatible->id, '10', '0.8'), new ActualOutputData($largeCompatible->id, '10', '0.8')],
        ));

        $this->assertSame('completed', $completed->status->value);
        $this->assertSame(7500, $completed->total_cost);
        $this->assertSame('9.000000', $this->balance($flour));
        $this->assertSame('0.980000', $this->balance($yeast));
        $this->assertSame('10.000000', $this->balance($smallBread));
        $this->assertSame('10.000000', $this->balance($largeBread));

        $reversed = app(ReverseProduction::class)->execute($completed, $owner, 'Registro duplicado durante la prueba.');

        $this->assertSame('reversed', $reversed->status->value);
        $this->assertSame('10.000000', $this->balance($flour));
        $this->assertSame('1.000000', $this->balance($yeast));
        $this->assertSame('0.000000', $this->balance($smallBread));
        $this->assertSame('0.000000', $this->balance($largeBread));
        $this->assertDatabaseHas('cost_allocations', ['production_order_id' => $order->id, 'cost_type' => 'labor']);
        $this->assertNotNull(CostAllocation::query()->where('production_order_id', $order->id)->firstOrFail()->reversed_at);
    }

    public function test_production_with_ingredient_shortage_cannot_start_without_authorization(): void
    {
        [$owner, $person, $version, , , $smallBread, $largeBread] = $this->context(withStock: false);
        $order = $this->plan($owner, $person, $version, $smallBread, $largeBread);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('se requiere una autorización');

        app(StartProduction::class)->execute($order, $owner);
    }

    /** @return array{User,Person,\App\Modules\Recipes\Domain\Models\RecipeVersion,ProductPresentation,ProductPresentation,ProductPresentation,ProductPresentation} */
    private function context(bool $withStock): array
    {
        Carbon::setTestNow('2026-09-15 08:00');
        app(EnsureCatalogReferenceData::class)->execute();
        app(EnsureAccessControlCatalog::class)->execute();
        $owner = User::factory()->create();
        $owner->roles()->attach(Role::query()->where('name', 'owner')->sole());
        $person = Person::factory()->create();
        $kg = Unit::query()->where('code', 'kg')->sole();
        $unit = Unit::query()->where('code', 'und')->sole();

        $flourItem = $this->item('HARINA-PROD', 'Harina producción', 'raw_material', $kg);
        $yeastItem = $this->item('LEV-PROD', 'Levadura producción', 'raw_material', $kg);
        $smallItem = $this->item('PAN-PROD-A', 'Pan pequeño', 'finished_product', $unit);
        $largeItem = $this->item('PAN-PROD-B', 'Pan grande', 'finished_product', $unit);
        $flour = $this->presentation($flourItem, $kg, 'HARINA-PROD-KG', true, false);
        $yeast = $this->presentation($yeastItem, $kg, 'LEV-PROD-KG', true, false);
        $smallBread = $this->presentation($smallItem, $unit, 'PAN-PROD-A-UND', false, true);
        $largeBread = $this->presentation($largeItem, $unit, 'PAN-PROD-B-UND', false, true);

        $recipe = app(CreateRecipe::class)->execute(new RecipeData('MASA-PROD', 'Masa de prueba', null, true, new RecipeVersionData(
            '1', $kg->id, '1.6', $kg->id, '0', 'Preparación de prueba.',
            [
                new RecipeIngredientData($flourItem->id, $flour->id, IngredientRole::Flour, '1', $kg->id, '100', false, 0),
                new RecipeIngredientData($yeastItem->id, $yeast->id, IngredientRole::Leavening, '0.02', $kg->id, '2', false, 1),
            ],
            [
                new CompatibleProductData($smallBread->id, '0.08', $kg->id, '0', '1', []),
                new CompatibleProductData($largeBread->id, '0.08', $kg->id, '0', '1', []),
            ],
        )), $owner);
        $version = $recipe->versions()->sole();
        app(PublishRecipeVersion::class)->execute($version, Carbon::parse('2026-09-01'), $owner);
        app(OpenCostPeriod::class)->execute(new OpenCostPeriodData(2026, 9, $owner, $this->utilities(), 5000, 'Tarifa estándar de prueba.'));

        if ($withStock) {
            $this->stock($owner, $flour, '10', 2000);
            $this->stock($owner, $yeast, '1', 10000);
        }

        return [$owner, $person, $version->fresh(), $flour, $yeast, $smallBread, $largeBread];
    }

    private function plan(User $owner, Person $person, $version, ProductPresentation $smallBread, ProductPresentation $largeBread): ProductionOrder
    {
        $smallCompatible = $version->compatibleProducts()->where('presentation_id', $smallBread->id)->sole();
        $largeCompatible = $version->compatibleProducts()->where('presentation_id', $largeBread->id)->sole();

        return app(PlanProduction::class)->execute(new PlanProductionData(
            $version->id,
            Carbon::parse('2026-09-15 09:00'),
            '1',
            LaborMethod::StandardPerKilogram,
            $person->id,
            $owner,
            [new PlannedOutputData($smallCompatible->id, '10'), new PlannedOutputData($largeCompatible->id, '10')],
        ));
    }

    private function item(string $code, string $name, string $type, Unit $unit): Item
    {
        return Item::create(['code' => $code, 'name' => $name, 'type' => $type, 'base_unit_id' => $unit->id, 'minimum_stock' => 0, 'allow_negative_stock' => false, 'is_active' => true]);
    }

    private function presentation(Item $item, Unit $unit, string $sku, bool $purchasable, bool $sellable): ProductPresentation
    {
        return $item->presentations()->create(['sku' => $sku, 'name' => 'Unidad de inventario', 'stock_unit_id' => $unit->id, 'conversion_to_item_base' => 1, 'is_purchasable' => $purchasable, 'is_sellable' => $sellable, 'is_stockable' => true, 'is_active' => true]);
    }

    private function stock(User $owner, ProductPresentation $presentation, string $quantity, int $cost): void
    {
        $adjustment = app(CreateInventoryAdjustment::class)->execute(new InventoryAdjustmentData('initial', now(), 'Saldo para producción de prueba.', $owner, [['presentation_id' => $presentation->id, 'counted_quantity' => $quantity, 'unit_cost' => $cost]]));
        app(ConfirmInventoryAdjustment::class)->execute($adjustment, $owner);
    }

    /** @return list<UtilityCostData> */
    private function utilities(): array
    {
        return [
            new UtilityCostData(UtilityType::Electricity, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'), Carbon::parse('2026-08-31'), 100, '100', '0', null, null, 100, 'Tarifa inicial de prueba.'),
            new UtilityCostData(UtilityType::Gas, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'), Carbon::parse('2026-08-31'), 200, '100', '0', null, null, 200, 'Tarifa inicial de prueba.'),
        ];
    }

    private function balance(ProductPresentation $presentation): string
    {
        return InventoryBalance::query()->where('presentation_id', $presentation->id)->sole()->physical_quantity;
    }
}
