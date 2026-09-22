<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Catalog\Application\CreateItem;
use App\Modules\Catalog\Application\CreatePresentation;
use App\Modules\Catalog\Application\SyncPackageComponents;
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
    public const RECIPE_CODE = 'DP-PAN-TAJADO-10KG-20260921';
    public const TAJADO_SKU = 'DP-PAN-TAJADO-410G';

    public function run(): void
    {
        $kg = Unit::query()->where('code', 'kg')->sole();
        $gram = Unit::query()->where('code', 'g')->sole();
        $milliliter = Unit::query()->where('code', 'ml')->sole();
        $unit = Unit::query()->where('code', 'und')->sole();
        $tray = Unit::query()->firstOrCreate(['code' => 'cub30'], ['name' => 'Cubeta de 30 unidades', 'dimension' => 'count', 'scale_to_base' => 30, 'precision' => 3, 'is_active' => true]);
        $halfCase = Unit::query()->firstOrCreate(['code' => 'mcaj6'], ['name' => 'Media caja de 6 unidades', 'dimension' => 'count', 'scale_to_base' => 6, 'precision' => 3, 'is_active' => true]);
        $actor = User::query()->where('email', DonPatyPeopleSeeder::KEVIN_EMAIL)->sole();

        $items = [
            'flour' => $this->material('DP-HARINA', 'Harina de trigo', ItemType::RawMaterial, $kg, '10'),
            'water' => $this->material('DP-AGUA', 'Agua para masa', ItemType::RawMaterial, $kg, '4.2'),
            'sugar' => $this->material('DP-AZUCAR', 'Azúcar', ItemType::RawMaterial, $kg, '1.3'),
            'salt' => $this->material('DP-SAL', 'Sal', ItemType::RawMaterial, $kg, '0.25'),
            'prodigio' => $this->material('DP-MANTEQUILLA-PRODIGIO', 'Mantequilla Prodigio', ItemType::RawMaterial, $kg, '0.926'),
            'hydrogenated' => $this->material('DP-MANTEQUILLA-HIDROGENADA', 'Mantequilla hidrogenada', ItemType::RawMaterial, $kg, '0.4'),
            'eggs' => $this->material('DP-HUEVOS', 'Huevos', ItemType::RawMaterial, $unit, '11'),
            'essence' => $this->material('DP-ESENCIA-MANTEQUILLA', 'Esencia de mantequilla', ItemType::RawMaterial, $milliliter, '20'),
            'vanilla' => $this->material('DP-ESENCIA-VAINILLA', 'Esencia de vainilla', ItemType::RawMaterial, $milliliter, '10'),
            'color' => $this->material('DP-COLOR', 'Color para pan', ItemType::RawMaterial, $gram, '1'),
            'yeast' => $this->material('DP-LEVADURA', 'Levadura', ItemType::RawMaterial, $gram, '120'),
            'antimold' => $this->material('DP-ANTIMOHO', 'Antimoho', ItemType::RawMaterial, $gram, '30'),
            'astra' => $this->material('DP-MANTEQUILLA-ASTRA', 'Mantequilla Astra para enmoldado', ItemType::Supply, $kg, '0.14'),
            'gourmet' => $this->material('DP-EMPASTE-GOURMET', 'Empaste Gourmet para hojaldrar', ItemType::RawMaterial, $kg, '0.35'),
            'bag' => $this->material('DP-BOLSA-TAJADO', 'Bolsa para pan tajado', ItemType::Packaging, $unit, '35'),
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
        $leftoverItem = $this->material('DP-SOBRANTE-MASA-PRODUCCION', 'Sobrante de masa de producción', ItemType::Intermediate, $kg, '0');
        $leftover = $this->presentation($leftoverItem, $kg, 'DP-SOBRANTE-MASA-KG', 'Kilogramo de masa reservada', false, false, '1');

        $cocaColaItem = $this->material('DP-COCACOLA-2L', 'Coca-Cola 2 L', ItemType::Resale, $unit, '0');
        $cocaCola = $this->presentation($cocaColaItem, $unit, 'DP-COCACOLA-2L-UND', 'Unidad de Coca-Cola 2 L', true, true, '1', 6000);
        $schweppesItem = $this->material('DP-SCHWEPPES-400ML', 'Schweppes soda 400 ml', ItemType::Resale, $unit, '0');
        $schweppes = $this->presentation($schweppesItem, $unit, 'DP-SCHWEPPES-400ML-UND', 'Unidad de Schweppes soda 400 ml', false, true, '1', 2500);
        $schweppesHalfCase = $this->presentation($schweppesItem, $halfCase, 'DP-SCHWEPPES-400ML-MCAJ6', 'Media caja de 6 unidades', true, false, '6');
        app(SyncPackageComponents::class)->execute($schweppesHalfCase, [['presentation_id' => $schweppes->id, 'quantity' => '6']]);

        if (! Recipe::query()->where('code', self::RECIPE_CODE)->exists()) {
            $recipe = app(CreateRecipe::class)->execute(new RecipeData(
                self::RECIPE_CODE,
                'Pan tajado — producción real del 21 de septiembre de 2026',
                'Base de 10 kg de harina. Esta versión conserva las cantidades reales del lote de 35 tajados y 509 g de masa reservada; las variaciones de cilindrado quedan documentadas en la producción.',
                true,
                new RecipeVersionData('10', $kg->id, '17.534', $kg->id, '0', 'Mezclar ingredientes, amasar, fermentar, enmoldar con Astra, hornear, enfriar, tajar y empacar. Registrar por separado la harina y Prodigio de cilindrado, los huevos adicionales y el destino de toda masa reservada.', [
                    $this->ingredient($items['flour'], IngredientRole::Flour, '10', $kg, '100', 0),
                    $this->ingredient($items['water'], IngredientRole::Liquid, '4.2', $kg, '42', 1),
                    $this->ingredient($items['sugar'], IngredientRole::Sweetener, '1.3', $kg, '13', 2),
                    $this->ingredient($items['salt'], IngredientRole::Salt, '0.25', $kg, '2.5', 3),
                    $this->ingredient($items['prodigio'], IngredientRole::Fat, '0.9', $kg, '9', 4),
                    $this->ingredient($items['hydrogenated'], IngredientRole::Fat, '0.4', $kg, '4', 5),
                    $this->ingredient($items['eggs'], IngredientRole::Other, '10', $unit, null, 6),
                    $this->ingredient($items['essence'], IngredientRole::Other, '20', $milliliter, null, 7),
                    $this->ingredient($items['color'], IngredientRole::Other, '1', $gram, null, 8),
                    $this->ingredient($items['yeast'], IngredientRole::Leavening, '120', $gram, '1.2', 9),
                    $this->ingredient($items['antimold'], IngredientRole::Other, '30', $gram, '0.3', 10),
                    $this->ingredient($items['astra'], IngredientRole::Other, '0.14', $kg, '1.4', 11),
                ], [
                    new CompatibleProductData($tajado->id, '0.486428', $kg->id, '15.7114', '1', [
                        new FinishingComponentData($items['bag']->id, '1', $unit->id),
                    ]),
                    new CompatibleProductData($leftover->id, '1', $kg->id, '0', '1', []),
                ], [
                    new RecipeBatchComponentData(RecipeBatchComponentType::InventoryConsumption, 'Gas de bombona — horneada de horno', $items['bakingGas']->id, '1', $unit->id, null, 0),
                ]),
            ), $actor);
            app(PublishRecipeVersion::class)->execute($recipe->versions()->sole(), Carbon::today(), $actor);
        }

        $this->prices($tajado, $cocaCola, $schweppes);
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

    private function prices(ProductPresentation $tajado, ProductPresentation $cocaCola, ProductPresentation $schweppes): void
    {
        foreach (['Mayorista' => 2600, 'Minorista' => 3500, 'Público' => 4000] as $listName => $price) {
            $list = PriceList::query()->firstOrCreate(
                ['name' => $listName],
                ['type' => $listName === 'Público' ? PriceListType::Other : PriceListType::Retail, 'is_default' => false, 'is_active' => true],
            );
            $list->items()->updateOrCreate(['presentation_id' => $tajado->id], ['price' => $price, 'minimum_price' => 2600]);
        }
        $retail = PriceList::query()->where('name', 'Minorista')->sole();
        $retail->items()->updateOrCreate(['presentation_id' => $cocaCola->id], ['price' => 6000, 'minimum_price' => 6000]);
        $retail->items()->updateOrCreate(['presentation_id' => $schweppes->id], ['price' => 2500, 'minimum_price' => 2500]);
    }
}
