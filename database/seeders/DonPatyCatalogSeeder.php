<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Catalog\Application\CreateItem;
use App\Modules\Catalog\Application\CreatePresentation;
use App\Modules\Catalog\Application\Data\ItemData;
use App\Modules\Catalog\Application\Data\PresentationData;
use App\Modules\Catalog\Domain\Enums\ItemType;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Pricing\Domain\Models\PriceList;
use App\Modules\Pricing\Domain\Enums\PriceListType;
use App\Modules\Recipes\Application\CreateRecipe;
use App\Modules\Recipes\Application\Data\CompatibleProductData;
use App\Modules\Recipes\Application\Data\FinishingComponentData;
use App\Modules\Recipes\Application\Data\RecipeData;
use App\Modules\Recipes\Application\Data\RecipeIngredientData;
use App\Modules\Recipes\Application\Data\RecipeBatchComponentData;
use App\Modules\Recipes\Application\Data\RecipeVersionData;
use App\Modules\Recipes\Application\PublishRecipeVersion;
use App\Modules\Recipes\Domain\Enums\IngredientRole;
use App\Modules\Recipes\Domain\Enums\RecipeBatchComponentType;
use App\Modules\Recipes\Domain\Models\Recipe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DonPatyCatalogSeeder extends Seeder
{
    public const RECIPE_CODE = 'DP-PAN-TAJADO-6KG';
    public const TAJADO_SKU = 'DP-PAN-TAJADO-410G';

    public function run(): void
    {
        $kg = Unit::query()->where('code', 'kg')->sole();
        $gram = Unit::query()->where('code', 'g')->sole();
        $milliliter = Unit::query()->where('code', 'ml')->sole();
        $unit = Unit::query()->where('code', 'und')->sole();
        $tray = Unit::query()->firstOrCreate(['code' => 'cub30'], ['name' => 'Cubeta de 30 unidades', 'dimension' => 'count', 'scale_to_base' => 30, 'precision' => 3, 'is_active' => true]);
        $actor = User::query()->where('email', DonPatyPeopleSeeder::KEVIN_EMAIL)->sole();

        $items = [
            'flour' => $this->material('DP-HARINA', 'Harina de trigo', ItemType::RawMaterial, $kg, '6'),
            'water' => $this->material('DP-AGUA', 'Agua para masa', ItemType::RawMaterial, $kg, '2.7'),
            'sugar' => $this->material('DP-AZUCAR', 'Azúcar', ItemType::RawMaterial, $kg, '0.78'),
            'salt' => $this->material('DP-SAL', 'Sal', ItemType::RawMaterial, $kg, '0.02'),
            'prodigio' => $this->material('DP-MANTEQUILLA-PRODIGIO', 'Mantequilla Prodigio', ItemType::RawMaterial, $kg, '0.51'),
            'hydrogenated' => $this->material('DP-MANTEQUILLA-HIDROGENADA', 'Mantequilla hidrogenada', ItemType::RawMaterial, $kg, '0.3'),
            'eggs' => $this->material('DP-HUEVOS', 'Huevos', ItemType::RawMaterial, $unit, '6'),
            'essence' => $this->material('DP-ESENCIA-MANTEQUILLA', 'Esencia de mantequilla', ItemType::RawMaterial, $milliliter, '10'),
            'vanilla' => $this->material('DP-ESENCIA-VAINILLA', 'Esencia de vainilla', ItemType::RawMaterial, $milliliter, '10'),
            'color' => $this->material('DP-COLOR', 'Color para pan', ItemType::RawMaterial, $gram, '1'),
            'yeast' => $this->material('DP-LEVADURA', 'Levadura', ItemType::RawMaterial, $gram, '100'),
            'antimold' => $this->material('DP-ANTIMOHO', 'Antimoho', ItemType::RawMaterial, $gram, '18'),
            'astra' => $this->material('DP-MANTEQUILLA-ASTRA', 'Mantequilla Astra para enmoldado', ItemType::Supply, $kg, '0.2'),
            'gourmet' => $this->material('DP-EMPASTE-GOURMET', 'Empaste Gourmet para hojaldrar', ItemType::RawMaterial, $kg, '0.35'),
            'bag' => $this->material('DP-BOLSA-TAJADO', 'Bolsa para pan tajado', ItemType::Packaging, $unit, '22'),
            'bakingGas' => $this->material('DP-GAS-BOMBONA-HORNEADA', 'Gas de bombona — horneada de horno', ItemType::Supply, $unit, '1'),
        ];

        foreach ($items as $key => $item) {
            $presentationUnit = match ($key) {
                'eggs' => $tray,
                'antimold' => $kg,
                default => $item->baseUnit,
            };
            $conversion = $key === 'eggs' ? '30' : '1';
            $this->presentation($item, $presentationUnit, $item->code.'-COMPRA', $presentationUnit->name, true, false, $conversion);
        }

        $tajadoItem = $this->material('DP-PAN-TAJADO', 'Pan tajado 410 g', ItemType::FinishedProduct, $unit, '22');
        $tajado = $this->presentation($tajadoItem, $unit, self::TAJADO_SKU, 'Unidad de 410 g', false, true, '1', 2600);

        if (! Recipe::query()->where('code', self::RECIPE_CODE)->exists()) {
            $recipe = app(CreateRecipe::class)->execute(new RecipeData(
                self::RECIPE_CODE,
                'Pan tajado — receta de 6 kg de harina',
                'Rendimiento estándar de 21,6 panes de 410 g. La harina adicional de cilindrado se registra como consumo real con novedad.',
                true,
                new RecipeVersionData('6', $kg->id, '10.729', $kg->id, '0', 'Mezclar ingredientes, amasar, fermentar, enmoldar con Astra, hornear, enfriar, tajar y empacar. Registrar harina adicional de cilindrado y el destino de cualquier remanente.', [
                    $this->ingredient($items['flour'], IngredientRole::Flour, '6', $kg, '100', 0),
                    $this->ingredient($items['water'], IngredientRole::Liquid, '2.7', $kg, '45', 1),
                    $this->ingredient($items['sugar'], IngredientRole::Sweetener, '0.78', $kg, '13', 2),
                    $this->ingredient($items['salt'], IngredientRole::Salt, '0.02', $kg, '0.3333', 3),
                    $this->ingredient($items['prodigio'], IngredientRole::Fat, '0.51', $kg, '8.5', 4),
                    $this->ingredient($items['hydrogenated'], IngredientRole::Fat, '0.3', $kg, '5', 5),
                    $this->ingredient($items['eggs'], IngredientRole::Other, '6', $unit, null, 6),
                    $this->ingredient($items['essence'], IngredientRole::Other, '10', $milliliter, null, 7),
                    $this->ingredient($items['color'], IngredientRole::Other, '1', $gram, null, 8),
                    $this->ingredient($items['yeast'], IngredientRole::Leavening, '100', $gram, '1.6667', 9),
                    $this->ingredient($items['antimold'], IngredientRole::Other, '18', $gram, '0.3', 10),
                    $this->ingredient($items['astra'], IngredientRole::Other, '0.2', $kg, '3.3333', 11),
                ], [
                    new CompatibleProductData($tajado->id, '0.496713', $kg->id, '17.4574', '1', [
                        new FinishingComponentData($items['bag']->id, '1', $unit->id),
                    ]),
                ], [
                    new RecipeBatchComponentData(RecipeBatchComponentType::InventoryConsumption, 'Gas de bombona — horneada de horno', $items['bakingGas']->id, '1', $unit->id, null, 0),
                ]),
            ), $actor);
            app(PublishRecipeVersion::class)->execute($recipe->versions()->sole(), Carbon::today(), $actor);
        }

        $this->prices($tajado);
    }

    private function material(string $code, string $name, ItemType $type, Unit $unit, string $minimumStock): Item
    {
        return Item::query()->firstOrCreate(['code' => $code], (new ItemData($code, $name, $type->value, $unit->id, $minimumStock, false, true, 'Dato operativo inicial de DonPaty.'))->attributes());
    }

    private function presentation(Item $item, Unit $stockUnit, string $sku, string $name, bool $purchasable, bool $sellable, string $conversion, ?int $minimumPrice = null): ProductPresentation
    {
        return ProductPresentation::query()->firstOrCreate(['sku' => $sku], (new PresentationData($sku, $name, $stockUnit->id, $conversion, $purchasable, $sellable, true, true, null, $minimumPrice))->attributes() + ['item_id' => $item->id]);
    }

    private function ingredient(Item $item, IngredientRole $role, string $quantity, Unit $unit, ?string $percentage, int $order): RecipeIngredientData
    {
        return new RecipeIngredientData($item->id, $item->presentations()->sole()->id, $role, $quantity, $unit->id, $percentage, false, $order);
    }

    private function prices(ProductPresentation $tajado): void
    {
        foreach (['Mayorista' => 2600, 'Minorista' => 3500, 'Público' => 4000] as $listName => $price) {
            $list = PriceList::query()->firstOrCreate(
                ['name' => $listName],
                ['type' => $listName === 'Público' ? PriceListType::Other : PriceListType::Retail, 'is_default' => false, 'is_active' => true],
            );
            $list->items()->updateOrCreate(['presentation_id' => $tajado->id], ['price' => $price, 'minimum_price' => 2600]);
        }
    }
}
