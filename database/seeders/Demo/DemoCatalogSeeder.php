<?php

namespace Database\Seeders\Demo;

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
use App\Modules\Recipes\Application\CreateNextRecipeVersion;
use App\Modules\Recipes\Application\CreateRecipe;
use App\Modules\Recipes\Application\Data\CompatibleProductData;
use App\Modules\Recipes\Application\Data\FinishingComponentData;
use App\Modules\Recipes\Application\Data\RecipeData;
use App\Modules\Recipes\Application\Data\RecipeIngredientData;
use App\Modules\Recipes\Application\Data\RecipeVersionData;
use App\Modules\Recipes\Application\PublishRecipeVersion;
use App\Modules\Recipes\Domain\Enums\IngredientRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoCatalogSeeder extends Seeder
{
    public const BASE_RECIPE = 'DEMO-MASA-TRAD';
    public const SLICED_RECIPE = 'DEMO-MASA-TAJ';

    public function run(): void
    {
        $kg = Unit::query()->where('code', 'kg')->sole();
        $unit = Unit::query()->where('code', 'und')->sole();
        $actor = User::query()->where('email', DemoPeopleSeeder::ADMIN_EMAIL)->sole();

        $flour = $this->item('DEMO-HARINA', 'Harina de trigo', ItemType::RawMaterial, $kg, '15');
        $water = $this->item('DEMO-AGUA', 'Agua para masa', ItemType::RawMaterial, $kg, '10');
        $sugar = $this->item('DEMO-AZUCAR', 'Azúcar', ItemType::RawMaterial, $kg, '2');
        $fat = $this->item('DEMO-MARGARINA', 'Margarina', ItemType::RawMaterial, $kg, '2');
        $salt = $this->item('DEMO-SAL', 'Sal', ItemType::RawMaterial, $kg, '1');
        $yeast = $this->item('DEMO-LEVADURA', 'Levadura', ItemType::RawMaterial, $kg, '0.5');
        $bag = $this->item('DEMO-BOLSA-TAJ', 'Bolsa para pan tajado', ItemType::Packaging, $unit, '10');

        foreach ([$flour, $water, $sugar, $fat, $salt, $yeast] as $ingredient) {
            $this->presentation($ingredient, $kg, $ingredient->code.'-KG', 'Kilogramo', true, false);
        }
        $this->presentation($bag, $unit, 'DEMO-BOLSA-TAJ-UND', 'Unidad', true, false);

        $cascarita = $this->finishedProduct('DEMO-PAN-CAS', 'Pan cascarita', 'DEMO-PAN-CAS-UND', 600, $unit);
        $bolita = $this->finishedProduct('DEMO-PAN-BOL', 'Pan bolita', 'DEMO-PAN-BOL-UND', 750, $unit);
        $tajado = $this->finishedProduct('DEMO-PAN-TAJ', 'Pan tajado', 'DEMO-PAN-TAJ-UND', 7000, $unit);

        $base = app(CreateRecipe::class)->execute(new RecipeData(
            self::BASE_RECIPE,
            'Masa base cascarita y bolita',
            'Una masa base que se distribuye entre dos productos terminados.',
            true,
            $this->baseVersion($kg, $flour, $water, $sugar, $fat, $salt, $yeast, $cascarita, $bolita),
        ), $actor);
        app(PublishRecipeVersion::class)->execute($base->versions()->sole(), Carbon::now()->startOfMonth()->subMonth(), $actor);
        $nextVersion = app(CreateNextRecipeVersion::class)->execute($base, $actor);
        app(PublishRecipeVersion::class)->execute($nextVersion, Carbon::now()->startOfMonth(), $actor);

        $sliced = app(CreateRecipe::class)->execute(new RecipeData(
            self::SLICED_RECIPE,
            'Masa de pan tajado',
            'Masa suave empacada individualmente después del horneado.',
            true,
            $this->slicedVersion($kg, $unit, $flour, $water, $sugar, $fat, $salt, $yeast, $bag, $tajado),
        ), $actor);
        app(PublishRecipeVersion::class)->execute($sliced->versions()->sole(), Carbon::now()->startOfMonth(), $actor);

        $this->prices($cascarita, $bolita, $tajado);
    }

    private function item(string $code, string $name, ItemType $type, Unit $unit, string $minimumStock): Item
    {
        return app(CreateItem::class)->execute(new ItemData(
            $code, $name, $type->value, $unit->id, $minimumStock, false, true, 'Dato demostrativo.',
        ));
    }

    private function presentation(Item $item, Unit $unit, string $sku, string $name, bool $purchasable, bool $sellable, ?int $minimumPrice = null): ProductPresentation
    {
        return app(CreatePresentation::class)->execute($item, new PresentationData(
            $sku, $name, $unit->id, '1', $purchasable, $sellable, true, true, null, $minimumPrice,
        ));
    }

    private function finishedProduct(string $code, string $name, string $sku, int $minimumPrice, Unit $unit): ProductPresentation
    {
        $item = $this->item($code, $name, ItemType::FinishedProduct, $unit, '20');

        return $this->presentation($item, $unit, $sku, 'Unidad', false, true, $minimumPrice);
    }

    private function baseVersion(Unit $kg, Item $flour, Item $water, Item $sugar, Item $fat, Item $salt, Item $yeast, ProductPresentation $cascarita, ProductPresentation $bolita): RecipeVersionData
    {
        return new RecipeVersionData('10', $kg->id, '17.6', $kg->id, '2.2727', 'Mezclar, amasar, fermentar, dividir, formar y hornear.', [
            $this->ingredient($flour, IngredientRole::Flour, '10', '100', $kg, 0),
            $this->ingredient($water, IngredientRole::Liquid, '5.5', '55', $kg, 1),
            $this->ingredient($sugar, IngredientRole::Sweetener, '1', '10', $kg, 2),
            $this->ingredient($fat, IngredientRole::Fat, '0.7', '7', $kg, 3),
            $this->ingredient($salt, IngredientRole::Salt, '0.2', '2', $kg, 4),
            $this->ingredient($yeast, IngredientRole::Leavening, '0.2', '2', $kg, 5),
        ], [
            new CompatibleProductData($cascarita->id, '0.06', $kg->id, '12', '1', []),
            new CompatibleProductData($bolita->id, '0.08', $kg->id, '12', '1', []),
        ]);
    }

    private function slicedVersion(Unit $kg, Unit $unit, Item $flour, Item $water, Item $sugar, Item $fat, Item $salt, Item $yeast, Item $bag, ProductPresentation $tajado): RecipeVersionData
    {
        return new RecipeVersionData('8', $kg->id, '14.52', $kg->id, '3.0303', 'Mezclar, amasar, fermentar, moldear, hornear, enfriar, cortar y empacar.', [
            $this->ingredient($flour, IngredientRole::Flour, '8', '100', $kg, 0),
            $this->ingredient($water, IngredientRole::Liquid, '4.8', '60', $kg, 1),
            $this->ingredient($sugar, IngredientRole::Sweetener, '0.8', '10', $kg, 2),
            $this->ingredient($fat, IngredientRole::Fat, '0.6', '7.5', $kg, 3),
            $this->ingredient($salt, IngredientRole::Salt, '0.16', '2', $kg, 4),
            $this->ingredient($yeast, IngredientRole::Leavening, '0.16', '2', $kg, 5),
        ], [
            new CompatibleProductData($tajado->id, '0.64', $kg->id, '12', '1', [
                new FinishingComponentData($bag->id, '1', $unit->id),
            ]),
        ]);
    }

    private function ingredient(Item $item, IngredientRole $role, string $quantity, string $percentage, Unit $unit, int $order): RecipeIngredientData
    {
        return new RecipeIngredientData(
            $item->id,
            $item->presentations()->sole()->id,
            $role,
            $quantity,
            $unit->id,
            $percentage,
            false,
            $order,
        );
    }

    private function prices(ProductPresentation $cascarita, ProductPresentation $bolita, ProductPresentation $tajado): void
    {
        $prices = [
            'Minorista' => [[$cascarita, 800, 600], [$bolita, 1000, 750], [$tajado, 9000, 7000]],
            'Mayorista' => [[$cascarita, 650, 600], [$bolita, 850, 750], [$tajado, 7800, 7000]],
        ];

        foreach ($prices as $listName => $items) {
            $list = PriceList::query()->where('name', $listName)->sole();
            foreach ($items as [$presentation, $price, $minimum]) {
                $list->items()->updateOrCreate(
                    ['presentation_id' => $presentation->id],
                    ['price' => $price, 'minimum_price' => $minimum],
                );
            }
        }
    }
}
