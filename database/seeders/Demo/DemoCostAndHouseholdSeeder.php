<?php

namespace Database\Seeders\Demo;

use App\Models\User;
use App\Modules\CostAccounting\Application\Data\OpenCostPeriodData;
use App\Modules\CostAccounting\Application\Data\UtilityCostData;
use App\Modules\CostAccounting\Application\OpenCostPeriod;
use App\Modules\CostAccounting\Domain\Enums\CostPeriodStatus;
use App\Modules\CostAccounting\Domain\Enums\UtilityType;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use App\Modules\Finance\Application\Data\RegisterExpenseData;
use App\Modules\Finance\Application\Data\RegisterIncomeData;
use App\Modules\Finance\Application\RegisterExpense;
use App\Modules\Finance\Application\RegisterIncome;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\FinancialCategory;
use App\Modules\Household\Application\ConfirmHouseholdBudget;
use App\Modules\Household\Application\CreateHouseholdAccount;
use App\Modules\Household\Application\Data\CreateHouseholdAccountData;
use App\Modules\Household\Application\Data\HouseholdTransactionData;
use App\Modules\Household\Application\Data\SaveHouseholdBudgetLineData;
use App\Modules\Household\Application\RecordHouseholdTransaction;
use App\Modules\Household\Application\SaveHouseholdBudgetLine;
use App\Modules\Household\Domain\Enums\HouseholdTransactionType;
use App\Modules\Household\Domain\Models\HouseholdBudget;
use DomainException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoCostAndHouseholdSeeder extends Seeder
{
    public const HOUSEHOLD_ACCOUNT = 'Dinero familiar demo';

    public function run(): void
    {
        $actor = User::query()->where('email', DemoPeopleSeeder::ADMIN_EMAIL)->sole();
        $cash = FinancialAccount::query()->where('code', '1105-CAJA')->sole();
        $monthStart = Carbon::now()->startOfMonth();

        app(RegisterIncome::class)->execute(new RegisterIncomeData(
            categoryId: FinancialCategory::query()->where('code', 'ING-OTROS')->sole()->id,
            personId: null,
            effectiveAt: $monthStart,
            dueAt: null,
            description: 'Capital inicial del escenario demostrativo.',
            totalAmount: 3_000_000,
            initialPaymentAmount: 3_000_000,
            financialAccountId: $cash->id,
            paymentReference: 'DEMO-CAPITAL',
            creator: $actor,
        ));

        $period = $this->openCostPeriod($actor, $monthStart);
        $this->registerBusinessUtilities($period, $cash, $actor);
        $this->seedHousehold($actor, $monthStart);
    }

    private function openCostPeriod(User $actor, Carbon $monthStart): CostPeriod
    {
        $existing = CostPeriod::query()->where('year', $monthStart->year)->where('month', $monthStart->month)->first();
        if ($existing) {
            if ($existing->status !== CostPeriodStatus::Open || $existing->utilities()->count() !== count(UtilityType::cases())) {
                throw new DomainException('El mes actual ya tiene un periodo incompatible con el escenario demo. Usa una base de demostración limpia.');
            }

            return $existing->fresh(['utilities', 'rates', 'poolEntries']);
        }

        $previous = $monthStart->copy()->subMonth();

        return app(OpenCostPeriod::class)->execute(new OpenCostPeriodData(
            year: $monthStart->year,
            month: $monthStart->month,
            opener: $actor,
            utilities: [
                new UtilityCostData(UtilityType::Electricity, $previous->copy()->startOfMonth(), $previous->copy()->endOfMonth(), $monthStart, 350_000, '70', '30', '420', 'DEMO-LUZ-350000', 8167, 'Tarifa demo basada en 30 kg estimados.'),
                new UtilityCostData(UtilityType::Gas, $previous->copy()->startOfMonth(), $previous->copy()->endOfMonth(), $monthStart, 80_000, '70', '30', '18', 'DEMO-GAS-80000', 1867, 'Tarifa demo basada en 30 kg estimados.'),
                new UtilityCostData(UtilityType::Water, $previous->copy()->startOfMonth(), $previous->copy()->endOfMonth(), $monthStart, 100_000, '70', '30', '25', 'DEMO-AGUA-100000', 2333, 'Tarifa demo basada en 30 kg estimados.'),
            ],
            standardLaborRatePerKg: 5000,
            laborRateReason: 'Tarifa demostrativa de mano de obra por kilogramo de harina.',
        ));
    }

    private function registerBusinessUtilities(CostPeriod $period, FinancialAccount $cash, User $actor): void
    {
        $categoryCodes = [
            UtilityType::Electricity->value => 'GAS-ELECTRICIDAD',
            UtilityType::Gas->value => 'GAS-GAS',
            UtilityType::Water->value => 'GAS-AGUA',
        ];

        foreach ($period->utilities as $utility) {
            $poolEntry = $period->poolEntries()->where('cost_type', $utility->utility_type->value)->sole();
            app(RegisterExpense::class)->execute(new RegisterExpenseData(
                categoryId: FinancialCategory::query()->where('code', $categoryCodes[$utility->utility_type->value])->sole()->id,
                personId: null,
                costPeriodId: $period->id,
                costPoolEntryId: $poolEntry->id,
                effectiveAt: $utility->paid_at,
                dueAt: null,
                description: 'Parte del negocio: recibo demo de '.$utility->utility_type->label().'.',
                totalAmount: $utility->business_amount,
                initialPaymentAmount: $utility->business_amount,
                financialAccountId: $cash->id,
                paymentReference: $utility->reference,
                creator: $actor,
            ));
        }
    }

    private function seedHousehold(User $actor, Carbon $monthStart): void
    {
        $worker = User::query()->where('email', DemoPeopleSeeder::WORKER_EMAIL)->sole();
        $shared = app(CreateHouseholdAccount::class)->execute(new CreateHouseholdAccountData(
            self::HOUSEHOLD_ACCOUNT,
            FinancialScope::Household,
            null,
            800_000,
            $actor,
        ));
        app(CreateHouseholdAccount::class)->execute(new CreateHouseholdAccountData(
            'Billetera de Patricia demo',
            FinancialScope::Personal,
            $worker->person_id,
            100_000,
            $actor,
        ));

        $services = FinancialCategory::query()->where('code', 'HOG-GAS-SERVICIOS')->sole();
        $food = FinancialCategory::query()->where('code', 'HOG-GAS-ALIMENTACION')->sole();
        app(SaveHouseholdBudgetLine::class)->execute(new SaveHouseholdBudgetLineData($monthStart->year, $monthStart->month, $services->id, null, 250_000, $actor));
        app(SaveHouseholdBudgetLine::class)->execute(new SaveHouseholdBudgetLineData($monthStart->year, $monthStart->month, $food->id, null, 300_000, $actor));
        app(ConfirmHouseholdBudget::class)->execute(
            HouseholdBudget::query()->where('year', $monthStart->year)->where('month', $monthStart->month)->sole(),
            $actor,
        );

        foreach ([
            [105_000, 'Parte del hogar: recibo demo de electricidad.'],
            [30_000, 'Parte del hogar: recibo demo de agua.'],
            [24_000, 'Parte del hogar: recibo demo de gas.'],
        ] as [$amount, $description]) {
            $this->householdExpense($shared, $services, $actor, $monthStart, $amount, $description);
        }
        $this->householdExpense($shared, $food, $actor, $monthStart->copy()->addDay(), 120_000, 'Mercado familiar demostrativo.');
    }

    private function householdExpense(FinancialAccount $account, FinancialCategory $category, User $actor, Carbon $date, int $amount, string $description): void
    {
        app(RecordHouseholdTransaction::class)->execute(new HouseholdTransactionData(
            HouseholdTransactionType::Expense,
            null,
            $category->id,
            $account->id,
            null,
            $date,
            $amount,
            $description,
            true,
            $actor,
        ));
    }
}
