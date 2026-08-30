<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Application\EnsureCatalogReferenceData;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Identity\Application\CreateAuthorizationRequest;
use App\Modules\Identity\Application\DecideAuthorizationRequest;
use App\Modules\Identity\Application\EnsureAccessControlCatalog;
use App\Modules\Identity\Domain\Enums\AuthorizationStatus;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Inventory\Application\ConfirmInventoryAdjustment;
use App\Modules\Inventory\Application\ConvertPackage;
use App\Modules\Inventory\Application\CreateInventoryAdjustment;
use App\Modules\Inventory\Application\Data\InventoryAdjustmentData;
use App\Modules\Inventory\Domain\Enums\PackageConversionType;
use App\Modules\Inventory\Domain\Models\InventoryBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_inventory_creates_a_valued_ledger_entry_and_balance(): void
    {
        [$owner, $presentation] = $this->context();
        $adjustment = $this->adjustment($owner, $presentation, 'initial', '25', 1800);

        app(ConfirmInventoryAdjustment::class)->execute($adjustment, $owner);

        $this->assertDatabaseHas('inventory_balances', [
            'presentation_id' => $presentation->id,
            'physical_quantity' => '25',
            'average_unit_cost' => 1800,
        ]);
        $this->assertDatabaseHas('inventory_movements', ['movement_type' => 'initial_balance', 'status' => 'confirmed']);
    }

    public function test_authorized_negative_adjustment_creates_a_traceable_incident(): void
    {
        [$owner, $presentation] = $this->context(allowNegative: true);
        $initial = $this->adjustment($owner, $presentation, 'initial', '2', 1000);
        app(ConfirmInventoryAdjustment::class)->execute($initial, $owner);

        $adjustment = $this->adjustment($owner, $presentation, 'manual', '-1', null);
        $requester = $this->userWithRole('administrator');
        $authorization = app(CreateAuthorizationRequest::class)->execute(
            'inventory.negative-adjustment',
            'inventory.authorize-negative',
            $adjustment,
            $requester,
            'Conteo físico negativo por movimientos pendientes.',
        );
        app(DecideAuthorizationRequest::class)->execute($authorization, $owner, AuthorizationStatus::Approved, 'Autorizado para regularizar.');

        app(ConfirmInventoryAdjustment::class)->execute($adjustment, $requester, $authorization->fresh());

        $this->assertDatabaseHas('inventory_balances', ['presentation_id' => $presentation->id, 'physical_quantity' => '-1']);
        $this->assertDatabaseHas('negative_stock_incidents', ['presentation_id' => $presentation->id, 'status' => 'pending']);
        $this->assertNotNull($authorization->fresh()->used_at);
    }

    public function test_assembling_and_disassembling_packages_conserves_quantity_and_cost(): void
    {
        [$owner, $single] = $this->context();
        $package = $this->presentation($single->item, $single->stockUnit, 'PAN-P12', 'Paquete x12');
        $package->packageComponents()->create(['component_presentation_id' => $single->id, 'quantity' => 12]);
        $initial = $this->adjustment($owner, $single, 'initial', '24', 500);
        app(ConfirmInventoryAdjustment::class)->execute($initial, $owner);

        app(ConvertPackage::class)->execute($package, PackageConversionType::Assembly, '2', $owner);
        $this->assertSame('0.000000', InventoryBalance::query()->where('presentation_id', $single->id)->sole()->physical_quantity);
        $this->assertSame('2.000000', InventoryBalance::query()->where('presentation_id', $package->id)->sole()->physical_quantity);
        $this->assertDatabaseHas('package_conversions', ['total_cost' => 12000, 'status' => 'confirmed']);

        app(ConvertPackage::class)->execute($package, PackageConversionType::Disassembly, '1', $owner);
        $this->assertSame('12.000000', InventoryBalance::query()->where('presentation_id', $single->id)->sole()->physical_quantity);
        $this->assertSame('1.000000', InventoryBalance::query()->where('presentation_id', $package->id)->sole()->physical_quantity);
    }

    /** @return array{User, ProductPresentation} */
    private function context(bool $allowNegative = false): array
    {
        app(EnsureCatalogReferenceData::class)->execute();
        $unit = Unit::query()->where('code', 'und')->sole();
        $item = Item::create([
            'code' => fake()->unique()->bothify('PAN-###'), 'name' => 'Pan prueba', 'type' => 'finished_product',
            'base_unit_id' => $unit->id, 'minimum_stock' => 0, 'allow_negative_stock' => $allowNegative,
            'is_active' => true,
        ]);

        return [$this->userWithRole('owner'), $this->presentation($item, $unit, 'PAN-UND', 'Unidad')];
    }

    private function presentation(Item $item, Unit $unit, string $sku, string $name): ProductPresentation
    {
        return $item->presentations()->create([
            'sku' => $sku, 'name' => $name, 'stock_unit_id' => $unit->id, 'conversion_to_item_base' => 1,
            'is_purchasable' => true, 'is_sellable' => true, 'is_stockable' => true, 'is_active' => true,
        ]);
    }

    private function adjustment(User $user, ProductPresentation $presentation, string $type, string $counted, ?int $unitCost): \App\Modules\Inventory\Domain\Models\InventoryAdjustment
    {
        return app(CreateInventoryAdjustment::class)->execute(new InventoryAdjustmentData(
            type: $type, effectiveAt: now(), reason: 'Conteo de prueba documentado.', creator: $user,
            lines: [['presentation_id' => $presentation->id, 'counted_quantity' => $counted, 'unit_cost' => $unitCost]],
        ));
    }

    private function userWithRole(string $roleName): User
    {
        app(EnsureAccessControlCatalog::class)->execute();
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', $roleName)->sole());

        return $user;
    }
}
