<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Application\CreateUnitConversion;
use App\Modules\Catalog\Application\EnsureCatalogReferenceData;
use App\Modules\Catalog\Application\SyncPackageComponents;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Identity\Application\EnsureAccessControlCatalog;
use App\Modules\Identity\Domain\Models\Role;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_units_convert_only_inside_the_same_dimension(): void
    {
        app(EnsureCatalogReferenceData::class)->execute();
        $kilogram = Unit::query()->where('code', 'kg')->sole();
        $gram = Unit::query()->where('code', 'g')->sole();
        $unit = Unit::query()->where('code', 'und')->sole();

        app(CreateUnitConversion::class)->execute($kilogram, $gram);

        $this->assertDatabaseHas('unit_conversions', [
            'from_unit_id' => $kilogram->id,
            'to_unit_id' => $gram->id,
            'factor' => '1000',
        ]);

        $this->expectException(DomainException::class);
        app(CreateUnitConversion::class)->execute($kilogram, $unit);
    }

    public function test_owner_can_create_items_presentations_and_same_product_packages(): void
    {
        app(EnsureCatalogReferenceData::class)->execute();
        $owner = $this->owner();
        $unit = Unit::query()->where('code', 'und')->sole();

        $this->actingAs($owner)->post(route('catalog.items.store'), [
            'code' => 'PAN-001',
            'name' => 'Pan aliñado',
            'type' => 'finished_product',
            'base_unit_id' => $unit->id,
            'minimum_stock' => 10,
            'allow_negative_stock' => false,
            'is_active' => true,
            'notes' => null,
        ])->assertRedirect();

        $item = Item::query()->where('code', 'PAN-001')->sole();
        $single = $this->presentation($item, $unit, 'PAN-UND', 'Unidad');
        $package = $this->presentation($item, $unit, 'PAN-P12', 'Paquete x12');

        app(SyncPackageComponents::class)->execute($package, [[
            'presentation_id' => $single->id,
            'quantity' => '12',
        ]]);

        $this->assertDatabaseHas('package_components', [
            'package_presentation_id' => $package->id,
            'component_presentation_id' => $single->id,
            'quantity' => '12',
        ]);
    }

    private function owner(): User
    {
        app(EnsureAccessControlCatalog::class)->execute();
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', 'owner')->sole());

        return $user;
    }

    private function presentation(Item $item, Unit $unit, string $sku, string $name): ProductPresentation
    {
        return $item->presentations()->create([
            'sku' => $sku,
            'name' => $name,
            'stock_unit_id' => $unit->id,
            'conversion_to_item_base' => 1,
            'is_purchasable' => false,
            'is_sellable' => true,
            'is_stockable' => true,
            'is_active' => true,
        ]);
    }
}
