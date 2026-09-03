<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Domain\Enums\ItemType;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Dashboard\Application\BusinessPerformanceQuery;
use App\Modules\Dashboard\Application\DashboardOverviewQuery;
use App\Modules\Dashboard\Application\Data\ReportDateRange;
use App\Modules\Finance\Application\Data\RegisterExpenseData;
use App\Modules\Finance\Application\Data\RegisterIncomeData;
use App\Modules\Finance\Application\RegisterExpense;
use App\Modules\Finance\Application\RegisterIncome;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\FinancialCategory;
use App\Modules\Identity\Application\EnsureAccessControlCatalog;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Inventory\Domain\Models\InventoryBalance;
use App\Modules\People\Domain\Models\Person;
use Database\Seeders\BusinessFinanceReferenceSeeder;
use Database\Seeders\CatalogReferenceSeeder;
use Database\Seeders\FinanceReferenceSeeder;
use Database\Seeders\HouseholdFinanceReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_report_separates_cash_flow_from_economic_result(): void
    {
        [$owner, $worker] = $this->context();
        $cash = FinancialAccount::query()->where('code', '1105-CAJA')->sole();
        app(RegisterIncome::class)->execute(new RegisterIncomeData(
            FinancialCategory::query()->where('code', 'ING-OTROS')->sole()->id,
            null,
            now(),
            null,
            'Ingreso del día',
            100000,
            100000,
            $cash->id,
            'Cobro inmediato',
            $owner,
        ));
        app(RegisterExpense::class)->execute(new RegisterExpenseData(
            FinancialCategory::query()->where('code', 'GAS-OTROS')->sole()->id,
            $worker->id,
            null,
            null,
            now(),
            now()->subDay(),
            'Gasto parcialmente pagado',
            50000,
            30000,
            $cash->id,
            'Pago inicial',
            $owner,
        ));

        $report = app(BusinessPerformanceQuery::class)->execute(ReportDateRange::dates(now()->toDateString(), now()->toDateString()));
        $dashboard = app(DashboardOverviewQuery::class)->execute($owner);

        $this->assertSame(70000, $report['totals']['cash_flow']);
        $this->assertSame(50000, $report['totals']['profit']);
        $this->assertTrue($report['provisional']);
        $this->assertSame(20000, $dashboard['financial']['obligations']['payables_total']);
        $this->assertSame(20000, $dashboard['financial']['obligations']['payables_overdue']);
        $this->assertNotEmpty($report['documents']);
    }

    public function test_operational_dashboard_masks_inventory_costs_on_the_server(): void
    {
        $this->context();
        $person = Person::factory()->create();
        $operator = User::factory()->create(['person_id' => $person->id]);
        $operator->roles()->attach(Role::query()->where('name', 'production')->sole());
        $unit = Unit::query()->where('code', 'kg')->sole();
        $item = Item::create(['code' => 'REP-HARINA', 'name' => 'Harina reporte', 'type' => ItemType::RawMaterial, 'base_unit_id' => $unit->id, 'minimum_stock' => 10, 'allow_negative_stock' => false, 'is_active' => true]);
        $presentation = ProductPresentation::create(['item_id' => $item->id, 'sku' => 'REP-HARINA-KG', 'name' => 'Kilogramo', 'stock_unit_id' => $unit->id, 'conversion_to_item_base' => 1, 'is_purchasable' => true, 'is_sellable' => false, 'is_stockable' => true, 'is_active' => true]);
        InventoryBalance::create(['presentation_id' => $presentation->id, 'physical_quantity' => 1, 'reserved_quantity' => 0, 'average_unit_cost' => 4500]);

        $dashboard = app(DashboardOverviewQuery::class)->execute($operator);
        $alert = $dashboard['operational']['inventory']['alerts'][0];

        $this->assertNull($dashboard['financial']);
        $this->assertNull($dashboard['household']);
        $this->assertNull($dashboard['operational']['inventory']['total_value']);
        $this->assertNull($alert['average_unit_cost']);
        $this->assertNull($alert['value']);
        $this->assertTrue($alert['is_low']);
    }

    /** @return array{User, Person} */
    private function context(): array
    {
        Carbon::setTestNow('2026-09-15 08:00');
        app(EnsureAccessControlCatalog::class)->execute();
        $this->seed(CatalogReferenceSeeder::class);
        $this->seed(FinanceReferenceSeeder::class);
        $this->seed(BusinessFinanceReferenceSeeder::class);
        $this->seed(HouseholdFinanceReferenceSeeder::class);
        $ownerPerson = Person::factory()->create();
        $worker = Person::factory()->create();
        $owner = User::factory()->create(['person_id' => $ownerPerson->id]);
        $owner->roles()->attach(Role::query()->where('name', 'owner')->sole());

        return [$owner, $worker];
    }
}
