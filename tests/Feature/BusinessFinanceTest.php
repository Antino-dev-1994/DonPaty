<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\CostAccounting\Application\CloseCostPeriod;
use App\Modules\CostAccounting\Application\Data\OpenCostPeriodData;
use App\Modules\CostAccounting\Application\Data\UtilityCostData;
use App\Modules\CostAccounting\Application\OpenCostPeriod;
use App\Modules\CostAccounting\Domain\Enums\CostType;
use App\Modules\CostAccounting\Domain\Enums\UtilityType;
use App\Modules\CostAccounting\Domain\Models\CostAllocation;
use App\Modules\Finance\Application\Data\RegisterExpenseData;
use App\Modules\Finance\Application\Data\RegisterIncomeData;
use App\Modules\Finance\Application\Data\SupplierPaymentData;
use App\Modules\Finance\Application\PaySupplierPayable;
use App\Modules\Finance\Application\RegisterExpense;
use App\Modules\Finance\Application\RegisterIncome;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\FinancialCategory;
use App\Modules\Identity\Application\EnsureAccessControlCatalog;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\People\Domain\Models\Person;
use Carbon\CarbonImmutable;
use Database\Seeders\BusinessFinanceReferenceSeeder;
use Database\Seeders\FinanceReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class BusinessFinanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_records_partial_expense_payment_and_monthly_cost_reconciliation(): void
    {
        Carbon::setTestNow('2026-09-25 08:00');
        app(EnsureAccessControlCatalog::class)->execute();
        $this->seed(FinanceReferenceSeeder::class);
        $this->seed(BusinessFinanceReferenceSeeder::class);
        $owner = User::factory()->create();
        $owner->roles()->attach(Role::query()->where('name', 'owner')->sole());
        $worker = Person::factory()->create();
        $cash = FinancialAccount::query()->where('code', '1105-CAJA')->sole();

        app(RegisterIncome::class)->execute(new RegisterIncomeData(
            FinancialCategory::query()->where('code', 'ING-OTROS')->sole()->id,
            null,
            now(),
            null,
            'Capital operativo de prueba.',
            100000,
            100000,
            $cash->id,
            'Ingreso inicial',
            $owner,
        ));
        $period = app(OpenCostPeriod::class)->execute(new OpenCostPeriodData(
            2026,
            9,
            $owner,
            [
                $this->utility(UtilityType::Electricity, 1000, 100),
                $this->utility(UtilityType::Gas, 2000, 200),
            ],
            500,
            'Tarifa inicial de prueba.',
        ));

        foreach ([[CostType::Electricity, 'GAS-ELECTRICIDAD'], [CostType::Gas, 'GAS-GAS']] as [$costType, $categoryCode]) {
            $poolEntry = $period->poolEntries()->where('cost_type', $costType)->sole();
            app(RegisterExpense::class)->execute(new RegisterExpenseData(
                FinancialCategory::query()->where('code', $categoryCode)->sole()->id,
                null,
                $period->id,
                $poolEntry->id,
                now(),
                null,
                "Factura de {$costType->label()}.",
                $poolEntry->amount,
                $poolEntry->amount,
                $cash->id,
                'Pago factura',
                $owner,
            ));
        }

        $labor = app(RegisterExpense::class)->execute(new RegisterExpenseData(
            FinancialCategory::query()->where('code', 'GAS-MANO-OBRA')->sole()->id,
            $worker->id,
            $period->id,
            null,
            now(),
            now()->addDays(5),
            'Pago de mano de obra del periodo.',
            5000,
            2000,
            $cash->id,
            'Primer pago',
            $owner,
        ));
        app(PaySupplierPayable::class)->execute($labor->payable()->sole(), new SupplierPaymentData(1000, $cash->id, now()->addDay(), $owner, 'Abono parcial'));

        $this->allocation($period->id, CostType::Electricity, 800);
        $this->allocation($period->id, CostType::Gas, 2200);
        $this->allocation($period->id, CostType::Labor, 2500);
        $closed = app(CloseCostPeriod::class)->execute($period, $owner);

        $this->assertSame('closed', $closed->status->value);
        $this->assertSame(2000, $labor->fresh()->balance_amount);
        $this->assertDatabaseHas('cost_variances', ['cost_period_id' => $period->id, 'cost_type' => 'electricity', 'variance_amount' => 200]);
        $this->assertDatabaseHas('cost_variances', ['cost_period_id' => $period->id, 'cost_type' => 'gas', 'variance_amount' => -200]);
        $this->assertDatabaseHas('cost_variances', ['cost_period_id' => $period->id, 'cost_type' => 'labor', 'actual_amount' => 3000, 'allocated_amount' => 2500, 'variance_amount' => 500]);
        $this->assertDatabaseHas('journal_entries', ['description' => 'Conciliación de costos 2026-09', 'status' => 'confirmed']);
    }

    private function utility(UtilityType $type, int $amount, int $rate): UtilityCostData
    {
        return new UtilityCostData(
            $type,
            CarbonImmutable::parse('2026-08-01'),
            CarbonImmutable::parse('2026-08-31'),
            CarbonImmutable::parse('2026-08-31'),
            $amount,
            '100',
            '0',
            null,
            "Factura {$type->value}",
            $rate,
            'Tarifa inicial sin historial.',
        );
    }

    private function allocation(string $periodId, CostType $type, int $amount): void
    {
        CostAllocation::create([
            'cost_period_id' => $periodId,
            'production_order_id' => (string) Str::ulid(),
            'cost_type' => $type->value,
            'base_quantity' => 1,
            'rate' => $amount,
            'amount' => $amount,
        ]);
    }
}
