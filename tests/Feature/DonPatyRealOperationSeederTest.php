<?php

namespace Tests\Feature;

use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Finance\Domain\Models\BusinessOpening;
use App\Modules\Inventory\Domain\Models\InventoryBalance;
use App\Modules\Production\Domain\Enums\ProductionStatus;
use App\Modules\Production\Domain\Models\ProductionOrder;
use App\Modules\Purchasing\Domain\Models\Purchase;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonPatyRealOperationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_loads_only_the_confirmed_operation_from_september_21(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 2);
        $opening = BusinessOpening::query()->sole();
        $this->assertSame('2026-09-21', $opening->opened_on->toDateString());
        $this->assertSame(257300, $opening->cash_amount);
        $this->assertSame(42300, $opening->nequi_amount);
        $this->assertDatabaseHas('purchases', ['supplier_document_number' => 'GAS-20260921', 'total' => 220000, 'paid_amount' => 120000, 'balance_amount' => 100000]);
        $this->assertDatabaseHas('purchases', ['supplier_document_number' => '00001', 'total' => 54148, 'paid_amount' => 54148, 'balance_amount' => 0]);
        $this->assertSame(2, Purchase::query()->count());

        $order = ProductionOrder::query()->sole();
        $this->assertSame(ProductionStatus::Completed, $order->status);
        $this->assertSame('17.534000', $order->actual_dough_quantity);
        $this->assertSame('35.000000', $order->outputs()->whereHas('presentation', fn ($query) => $query->where('sku', 'DP-PAN-TAJADO-410G'))->sole()->quantity);
        $this->assertSame('0.509020', $order->outputs()->whereHas('presentation', fn ($query) => $query->where('sku', 'DP-SOBRANTE-MASA-KG'))->sole()->quantity);

        $this->assertBalance('DP-BOLSA-TAJADO-COMPRA', '70.000000');
        $this->assertBalance('DP-GAS-BOMBONA-HORNEADA-COMPRA', '43.000000');
        $this->assertBalance('DP-COCACOLA-2L-UND', '9.000000');
        $this->assertBalance('DP-SCHWEPPES-400ML-UND', '6.000000');
        $this->assertBalance('DP-SOBRANTE-MASA-KG', '0.509020');
    }

    private function assertBalance(string $sku, string $expected): void
    {
        $presentation = ProductPresentation::query()->where('sku', $sku)->sole();
        $balance = InventoryBalance::query()->where('presentation_id', $presentation->id)->sole();

        $this->assertSame($expected, $balance->physical_quantity, $sku);
    }
}
