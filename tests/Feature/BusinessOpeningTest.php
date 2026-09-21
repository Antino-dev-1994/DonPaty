<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Finance\Application\Data\RegisterBusinessOpeningData;
use App\Modules\Finance\Application\RegisterBusinessOpening;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Identity\Application\EnsureAccessControlCatalog;
use Database\Seeders\FinanceReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BusinessOpeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_registers_initial_capital_in_cash_and_nequi_without_creating_revenue(): void
    {
        app(EnsureAccessControlCatalog::class)->execute();
        $this->seed(FinanceReferenceSeeder::class);
        $user = User::factory()->create();

        $opening = app(RegisterBusinessOpening::class)->execute(new RegisterBusinessOpeningData(
            Carbon::parse('2026-09-21'), 257300, 42300, 'Saldos iniciales reales.', $user,
        ));

        $this->assertDatabaseHas('business_openings', ['id' => $opening->id, 'cash_amount' => 257300, 'nequi_amount' => 42300]);
        $this->assertDatabaseHas('journal_lines', ['journal_entry_id' => $opening->journal_entry_id, 'financial_account_id' => FinancialAccount::query()->where('code', '1105-CAJA')->sole()->id, 'debit_amount' => 257300, 'credit_amount' => 0]);
        $this->assertDatabaseHas('journal_lines', ['journal_entry_id' => $opening->journal_entry_id, 'financial_account_id' => FinancialAccount::query()->where('code', '1110-NEQUI')->sole()->id, 'debit_amount' => 42300, 'credit_amount' => 0]);
        $this->assertDatabaseHas('journal_lines', ['journal_entry_id' => $opening->journal_entry_id, 'financial_account_id' => FinancialAccount::query()->where('code', '3105-CAPITAL-APORTADO')->sole()->id, 'debit_amount' => 0, 'credit_amount' => 299600]);
    }
}
