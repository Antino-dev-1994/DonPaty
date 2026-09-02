<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\CostAccounting\Domain\Enums\CostPeriodStatus;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use App\Modules\Finance\Application\Data\RegisterIncomeData;
use App\Modules\Finance\Application\RegisterIncome;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\FinancialCategory;
use App\Modules\Household\Application\ConfirmFundRequest;
use App\Modules\Household\Application\ContributeToSavingsGoal;
use App\Modules\Household\Application\CreateDebt;
use App\Modules\Household\Application\CreateFundRequest;
use App\Modules\Household\Application\CreateHouseholdAccount;
use App\Modules\Household\Application\CreateSavingsGoal;
use App\Modules\Household\Application\Data\ContributeToSavingsGoalData;
use App\Modules\Household\Application\Data\CreateDebtData;
use App\Modules\Household\Application\Data\CreateFundRequestData;
use App\Modules\Household\Application\Data\CreateHouseholdAccountData;
use App\Modules\Household\Application\Data\CreateSavingsGoalData;
use App\Modules\Household\Application\Data\HouseholdTransactionData;
use App\Modules\Household\Application\Data\PayDebtData;
use App\Modules\Household\Application\Data\PayFundRequestData;
use App\Modules\Household\Application\Data\SaveHouseholdBudgetLineData;
use App\Modules\Household\Application\DecideFundRequest;
use App\Modules\Household\Application\HouseholdBudgetReport;
use App\Modules\Household\Application\HouseholdVisibility;
use App\Modules\Household\Application\PayDebt;
use App\Modules\Household\Application\PayFundRequest;
use App\Modules\Household\Application\RecordHouseholdTransaction;
use App\Modules\Household\Application\SaveHouseholdBudgetLine;
use App\Modules\Household\Domain\Enums\DebtDirection;
use App\Modules\Household\Domain\Enums\FundRequestStatus;
use App\Modules\Household\Domain\Enums\HouseholdTransactionType;
use App\Modules\Household\Domain\Models\HouseholdBudget;
use App\Modules\Household\Domain\Models\HouseholdTransaction;
use App\Modules\Identity\Application\EnsureAccessControlCatalog;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\People\Domain\Models\Person;
use Database\Seeders\BusinessFinanceReferenceSeeder;
use Database\Seeders\FinanceReferenceSeeder;
use Database\Seeders\HouseholdFinanceReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HouseholdFinanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_fund_request_business_payment_and_budget_execution(): void
    {
        [$owner, $resident] = $this->context();
        $personal = app(CreateHouseholdAccount::class)->execute(new CreateHouseholdAccountData('Billetera personal', FinancialScope::Personal, $resident->person_id, 0, $owner));
        app(CreateHouseholdAccount::class)->execute(new CreateHouseholdAccountData('Dinero familiar', FinancialScope::Household, null, 50000, $owner));
        $businessCash = FinancialAccount::query()->where('code', '1105-CAJA')->sole();
        app(RegisterIncome::class)->execute(new RegisterIncomeData(
            FinancialCategory::query()->where('code', 'ING-OTROS')->sole()->id,
            null,
            now(),
            null,
            'Dinero de prueba del negocio',
            100000,
            100000,
            $businessCash->id,
            'Saldo de prueba',
            $owner,
        ));
        CostPeriod::create(['year' => 2026, 'month' => 9, 'status' => CostPeriodStatus::Open, 'opened_at' => now(), 'opened_by' => $owner->id]);

        $fundRequest = app(CreateFundRequest::class)->execute(new CreateFundRequestData($resident->person_id, FinancialScope::Business, 10000, 'Pago de trabajo realizado', now(), $resident));
        app(DecideFundRequest::class)->execute($fundRequest, FundRequestStatus::Approved, $owner);
        app(PayFundRequest::class)->execute($fundRequest->fresh(), new PayFundRequestData($businessCash->id, $personal->id, now(), $owner));
        app(ConfirmFundRequest::class)->execute($fundRequest->fresh(), $resident);

        $food = FinancialCategory::query()->where('code', 'HOG-GAS-ALIMENTACION')->sole();
        app(SaveHouseholdBudgetLine::class)->execute(new SaveHouseholdBudgetLineData(2026, 9, $food->id, $resident->person_id, 8000, $owner));
        app(RecordHouseholdTransaction::class)->execute(new HouseholdTransactionData(HouseholdTransactionType::Expense, $resident->person_id, $food->id, $personal->id, null, now(), 3000, 'Compra de alimentos', true, $owner));
        $report = app(HouseholdBudgetReport::class)->execute(HouseholdBudget::query()->sole(), $resident)->sole();

        $this->assertSame('confirmed', $fundRequest->fresh()->status->value);
        $this->assertDatabaseHas('expense_records', ['person_id' => $resident->person_id, 'total_amount' => 10000, 'paid_amount' => 10000]);
        $this->assertDatabaseHas('cost_pool_entries', ['cost_type' => 'labor', 'amount' => 10000]);
        $this->assertSame(3000, $report['executed_amount']);
        $this->assertSame(5000, $report['available_amount']);
        $visibleAccounts = app(HouseholdVisibility::class)->accounts(FinancialAccount::query(), $resident)->pluck('id');
        $this->assertEquals([$personal->id], $visibleAccounts->all());
        $this->assertSame(2, app(HouseholdVisibility::class)->transactions(HouseholdTransaction::query(), $resident)->count());
    }

    public function test_debt_payment_separates_interest_and_savings_are_internal_transfers(): void
    {
        [$owner] = $this->context();
        $cash = app(CreateHouseholdAccount::class)->execute(new CreateHouseholdAccountData('Caja del hogar', FinancialScope::Household, null, 100000, $owner));
        $savings = app(CreateHouseholdAccount::class)->execute(new CreateHouseholdAccountData('Ahorro reservado', FinancialScope::Household, null, 0, $owner));
        $debt = app(CreateDebt::class)->execute(new CreateDebtData(DebtDirection::Payable, null, 'Préstamo familiar', 12000, 12, Carbon::parse('2026-08-01'), Carbon::parse('2026-09-01'), 2, $cash->id, $owner));
        $payment = app(PayDebt::class)->execute($debt, new PayDebtData(6120, $cash->id, now(), $owner));
        $goal = app(CreateSavingsGoal::class)->execute(new CreateSavingsGoalData('Fondo de emergencia', null, $savings->id, 20000, null, $owner));
        app(ContributeToSavingsGoal::class)->execute($goal, new ContributeToSavingsGoalData($cash->id, 5000, now(), $owner));

        $this->assertSame(6000, $payment->principal_amount);
        $this->assertSame(120, $payment->interest_amount);
        $this->assertSame('paid', $debt->installments()->orderBy('sequence')->first()->status->value);
        $this->assertSame(5000, (int) $goal->contributions()->sum('amount'));
        $this->assertSame('active', $goal->fresh()->status->value);
        $this->assertSame(0, HouseholdTransaction::query()->count());
        $this->assertDatabaseHas('journal_entries', ['description' => 'Aporte a meta Fondo de emergencia', 'status' => 'confirmed']);
    }

    /** @return array{User, User} */
    private function context(): array
    {
        Carbon::setTestNow('2026-09-15 08:00');
        app(EnsureAccessControlCatalog::class)->execute();
        $this->seed(FinanceReferenceSeeder::class);
        $this->seed(BusinessFinanceReferenceSeeder::class);
        $this->seed(HouseholdFinanceReferenceSeeder::class);
        $ownerPerson = Person::factory()->create();
        $residentPerson = Person::factory()->create();
        $owner = User::factory()->create(['person_id' => $ownerPerson->id]);
        $resident = User::factory()->create(['person_id' => $residentPerson->id]);
        $owner->roles()->attach(Role::query()->where('name', 'owner')->sole());
        $resident->roles()->attach(Role::query()->where('name', 'resident')->sole());

        return [$owner, $resident];
    }
}
