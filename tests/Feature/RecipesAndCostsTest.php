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
use App\Modules\Identity\Application\EnsureAccessControlCatalog;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Recipes\Application\CreateNextRecipeVersion;
use App\Modules\Recipes\Application\CreateRecipe;
use App\Modules\Recipes\Application\Data\CompatibleProductData;
use App\Modules\Recipes\Application\Data\RecipeData;
use App\Modules\Recipes\Application\Data\RecipeIngredientData;
use App\Modules\Recipes\Application\Data\RecipeVersionData;
use App\Modules\Recipes\Application\PublishRecipeVersion;
use App\Modules\Recipes\Application\SaveDraftRecipeVersion;
use App\Modules\Recipes\Application\ScaleRecipeVersion;
use App\Modules\Recipes\Domain\Enums\IngredientRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RecipesAndCostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipe_versions_keep_history_and_scale_from_decimal_flour(): void
    {
        [$owner, $kilogram, $gram, $flour, $yeast, $product] = $this->recipeContext();
        $recipe = app(CreateRecipe::class)->execute(new RecipeData('MASA-01', 'Masa de pan', null, true, $this->versionData($kilogram, $gram, $flour, $yeast, $product, '0.020')), $owner);
        $first = $recipe->versions()->sole();
        app(PublishRecipeVersion::class)->execute($first, Carbon::parse('2026-08-01'), $owner);

        $scaled = app(ScaleRecipeVersion::class)->execute($first->fresh(), '2.5', $kilogram);
        $this->assertSame('4.000000', $scaled['expected_dough_yield']);
        $this->assertSame('50.000', $scaled['compatible_products']->first()['estimated_units']);

        $second = app(CreateNextRecipeVersion::class)->execute($recipe, $owner);
        app(SaveDraftRecipeVersion::class)->execute($second, $this->versionData($kilogram, $gram, $flour, $yeast, $product, '0.030'));
        app(PublishRecipeVersion::class)->execute($second, Carbon::parse('2026-09-01'), $owner);

        $this->assertSame('0.020000', $first->fresh('ingredients')->ingredients->firstWhere('item_id', $yeast->id)->quantity);
        $this->assertSame('2026-08-31', $first->fresh()->effective_to->toDateString());
        $this->assertSame('0.030000', $second->fresh('ingredients')->ingredients->firstWhere('item_id', $yeast->id)->quantity);
    }

    public function test_first_cost_period_uses_manual_rates_and_next_period_suggests_from_flour_history(): void
    {
        $owner = $this->owner();
        $august = app(OpenCostPeriod::class)->execute(new OpenCostPeriodData(2026, 8, $owner, $this->utilities('2026-07-01', '2026-07-31', 100000, 60, 700, 500, 300)));
        $august->update(['processed_flour_quantity' => 100]);

        $september = app(OpenCostPeriod::class)->execute(new OpenCostPeriodData(2026, 9, $owner, $this->utilities('2026-08-01', '2026-08-31', 120000, 50, null, null, null)));
        $electricity = $september->rates()->where('cost_type', UtilityType::Electricity)->sole();

        $this->assertSame(600, $electricity->suggested_rate);
        $this->assertSame(600, $electricity->effective_rate);
        $this->assertSame('suggested', $electricity->method->value);
        $this->assertDatabaseHas('utility_cost_records', ['cost_period_id' => $september->id, 'business_amount' => 60000, 'household_amount' => 60000]);
    }

    private function versionData(Unit $kilogram, Unit $gram, Item $flour, Item $yeast, ProductPresentation $product, string $yeastQuantity): RecipeVersionData
    {
        return new RecipeVersionData('1', $kilogram->id, '1.6', $kilogram->id, '2', 'Mezclar y amasar.', [
            new RecipeIngredientData($flour->id, null, IngredientRole::Flour, '1', $kilogram->id, '100', false, 0),
            new RecipeIngredientData($yeast->id, null, IngredientRole::Leavening, $yeastQuantity, $kilogram->id, bcmul($yeastQuantity, '100', 4), false, 1),
        ], [new CompatibleProductData($product->id, '80', $gram->id, '12', '1', [])]);
    }

    /** @return array{User,Unit,Unit,Item,Item,ProductPresentation} */
    private function recipeContext(): array
    {
        app(EnsureCatalogReferenceData::class)->execute(); $owner = $this->owner(); $kg = Unit::query()->where('code', 'kg')->sole(); $g = Unit::query()->where('code', 'g')->sole();
        $flour = Item::create(['code' => 'HARINA-T', 'name' => 'Harina', 'type' => 'raw_material', 'base_unit_id' => $kg->id, 'minimum_stock' => 0, 'allow_negative_stock' => false, 'is_active' => true]);
        $yeast = Item::create(['code' => 'LEV-T', 'name' => 'Levadura', 'type' => 'raw_material', 'base_unit_id' => $g->id, 'minimum_stock' => 0, 'allow_negative_stock' => false, 'is_active' => true]);
        $bread = Item::create(['code' => 'PAN-T', 'name' => 'Pan', 'type' => 'finished_product', 'base_unit_id' => Unit::query()->where('code', 'und')->sole()->id, 'minimum_stock' => 0, 'allow_negative_stock' => false, 'is_active' => true]);
        $product = $bread->presentations()->create(['sku' => 'PAN-T-UND', 'name' => 'Unidad', 'stock_unit_id' => Unit::query()->where('code', 'und')->sole()->id, 'conversion_to_item_base' => 1, 'is_purchasable' => false, 'is_sellable' => true, 'is_stockable' => true, 'is_active' => true]);
        return [$owner, $kg, $g, $flour, $yeast, $product];
    }

    /** @return list<UtilityCostData> */
    private function utilities(string $from, string $to, int $total, int $businessPercentage, ?int $electricityRate, ?int $gasRate, ?int $waterRate): array
    {
        return [
            new UtilityCostData(UtilityType::Electricity, Carbon::parse($from), Carbon::parse($to), Carbon::parse($to), $total, (string) $businessPercentage, (string) (100 - $businessPercentage), null, null, $electricityRate, $electricityRate === null ? null : 'Tarifa inicial sin historial.'),
            new UtilityCostData(UtilityType::Gas, Carbon::parse($from), Carbon::parse($to), Carbon::parse($to), $total, (string) $businessPercentage, (string) (100 - $businessPercentage), null, null, $gasRate, $gasRate === null ? null : 'Tarifa inicial sin historial.'),
            new UtilityCostData(UtilityType::Water, Carbon::parse($from), Carbon::parse($to), Carbon::parse($to), $total, (string) $businessPercentage, (string) (100 - $businessPercentage), null, null, $waterRate, $waterRate === null ? null : 'Tarifa inicial sin historial.'),
        ];
    }

    private function owner(): User
    {
        app(EnsureAccessControlCatalog::class)->execute(); $user = User::factory()->create(); $user->roles()->attach(Role::query()->where('name', 'owner')->sole()); return $user;
    }
}
