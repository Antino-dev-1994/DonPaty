<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\CashManagement\Application\CloseCashSession;
use App\Modules\CashManagement\Application\OpenCashSession;
use App\Modules\Catalog\Application\EnsureCatalogReferenceData;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Customers\Domain\Models\CustomerProfile;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Identity\Application\EnsureAccessControlCatalog;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Inventory\Application\ConfirmInventoryAdjustment;
use App\Modules\Inventory\Application\CreateInventoryAdjustment;
use App\Modules\Inventory\Application\Data\InventoryAdjustmentData;
use App\Modules\Inventory\Domain\Models\InventoryBalance;
use App\Modules\People\Domain\Models\Person;
use App\Modules\Pricing\Application\EnsureDefaultPriceLists;
use App\Modules\Pricing\Domain\Models\PriceList;
use App\Modules\Sales\Application\ConfirmSale;
use App\Modules\Sales\Application\CreateDraftSale;
use App\Modules\Sales\Application\Data\CreateSaleData;
use App\Modules\Sales\Application\Data\SaleLineData;
use App\Modules\Sales\Application\Data\SalePaymentPlanData;
use App\Modules\Sales\Application\Data\SaleReturnLineData;
use App\Modules\Sales\Application\PayReceivable;
use App\Modules\Sales\Application\RegisterSaleReturn;
use App\Modules\Sales\Domain\Models\Receivable;
use Carbon\CarbonImmutable;
use Database\Seeders\FinanceReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SalesAndCashTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_sale_disassembles_package_and_return_updates_inventory_and_cash_session(): void
    {
        $context = $this->context();
        $this->stock($context['owner'], $context['package'], '1', 6000);
        $pettyCash = FinancialAccount::query()->where('code', '1105-CAJA-MENOR')->sole();
        $session = app(OpenCashSession::class)->execute($pettyCash->id, 0, $context['owner']);

        $sale = app(CreateDraftSale::class)->execute(new CreateSaleData(
            null,
            null,
            $context['priceList']->id,
            now(),
            null,
            0,
            $context['owner'],
            [new SaleLineData($context['single']->id, '5', null, 0, null)],
            [new SalePaymentPlanData($pettyCash->id, 5000, 'Efectivo')],
        ));
        $sale = app(ConfirmSale::class)->execute($sale, $context['owner']);

        $this->assertSame('0.000000', InventoryBalance::query()->where('presentation_id', $context['package']->id)->sole()->physical_quantity);
        $this->assertSame('7.000000', InventoryBalance::query()->where('presentation_id', $context['single']->id)->sole()->physical_quantity);
        $this->assertSame(2500, $sale->cost_of_goods_sold);

        app(RegisterSaleReturn::class)->execute(
            $sale,
            [new SaleReturnLineData($sale->lines->sole()->id, '2', true, 'Producto en buen estado.')],
            now()->addHour(),
            'Cliente cambió la cantidad requerida.',
            $context['owner'],
            $pettyCash->id,
        );
        $closed = app(CloseCashSession::class)->execute($session, 3000, '', $context['owner']);

        $this->assertSame('9.000000', InventoryBalance::query()->where('presentation_id', $context['single']->id)->sole()->physical_quantity);
        $this->assertSame(3000, $closed->expected_closing_amount);
        $this->assertSame(0, $closed->difference_amount);
        $this->assertDatabaseHas('payments', ['direction' => 'outgoing', 'amount' => 2000, 'status' => 'confirmed']);
        $this->assertDatabaseHas('sale_returns', ['sale_id' => $sale->id, 'total_refund' => 2000]);
    }

    public function test_credit_sale_creates_receivable_and_accepts_partial_payment(): void
    {
        $context = $this->context(withCustomer: true);
        $this->stock($context['owner'], $context['single'], '10', 500);
        $sale = app(CreateDraftSale::class)->execute(new CreateSaleData(
            $context['customer']->id,
            null,
            $context['priceList']->id,
            now(),
            now()->addDays(8),
            0,
            $context['owner'],
            [new SaleLineData($context['single']->id, '5', null, 0, null)],
            [],
        ));
        app(ConfirmSale::class)->execute($sale, $context['owner']);
        $receivable = Receivable::query()->where('source_id', $sale->id)->sole();

        $payment = app(PayReceivable::class)->execute(
            $receivable,
            2000,
            FinancialAccount::query()->where('code', '1105-CAJA')->sole()->id,
            now()->addDay(),
            $context['owner'],
            'Abono parcial',
        );

        $this->assertSame(3000, $receivable->fresh()->balance_amount);
        $this->assertSame('partial', $receivable->fresh()->status->value);
        $this->assertSame(3000, $sale->fresh()->balance_amount);
        $this->assertDatabaseHas('journal_entries', ['description' => "Abono {$payment->document_number}"]);
    }

    /** @return array<string, mixed> */
    private function context(bool $withCustomer = false): array
    {
        Carbon::setTestNow('2026-09-20 08:00');
        app(EnsureCatalogReferenceData::class)->execute();
        app(EnsureAccessControlCatalog::class)->execute();
        app(EnsureDefaultPriceLists::class)->execute();
        $this->seed(FinanceReferenceSeeder::class);

        $owner = User::factory()->create();
        $owner->roles()->attach(Role::query()->where('name', 'owner')->sole());
        $unit = Unit::query()->where('code', 'und')->sole();
        $item = Item::create([
            'code' => 'VEN-PAN',
            'name' => 'Pan venta',
            'type' => 'finished_product',
            'base_unit_id' => $unit->id,
            'minimum_stock' => 0,
            'allow_negative_stock' => false,
            'is_active' => true,
        ]);
        $single = $this->presentation($item, $unit, 'VEN-PAN-UND', 'Unidad');
        $package = $this->presentation($item, $unit, 'VEN-PAN-P12', 'Paquete x12');
        $package->packageComponents()->create(['component_presentation_id' => $single->id, 'quantity' => 12]);
        $priceList = PriceList::query()->where('is_default', true)->sole();
        $priceList->items()->create(['presentation_id' => $single->id, 'price' => 1000, 'minimum_price' => 800]);

        $customer = null;
        if ($withCustomer) {
            $person = Person::factory()->create();
            $customer = CustomerProfile::create([
                'person_id' => $person->id,
                'default_price_list_id' => $priceList->id,
                'credit_limit' => 100000,
                'default_payment_term_days' => 8,
                'is_active' => true,
            ]);
        }

        return compact('owner', 'single', 'package', 'priceList', 'customer');
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

    private function stock(User $owner, ProductPresentation $presentation, string $quantity, int $unitCost): void
    {
        $adjustment = app(CreateInventoryAdjustment::class)->execute(new InventoryAdjustmentData(
            'initial',
            CarbonImmutable::now(),
            'Saldo inicial de prueba.',
            $owner,
            [['presentation_id' => $presentation->id, 'counted_quantity' => $quantity, 'unit_cost' => $unitCost]],
        ));
        app(ConfirmInventoryAdjustment::class)->execute($adjustment, $owner);
    }
}
