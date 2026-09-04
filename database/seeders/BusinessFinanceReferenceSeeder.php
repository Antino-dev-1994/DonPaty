<?php

namespace Database\Seeders;

use App\Modules\CostAccounting\Domain\Enums\CostType;
use App\Modules\Finance\Domain\Enums\FinancialCategoryType;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\FinancialCategory;
use Illuminate\Database\Seeder;

class BusinessFinanceReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $this->category('ING-OTROS', 'Otros ingresos', FinancialCategoryType::Income, '4210-OTROS-INGRESOS');
        $this->category('GAS-MANO-OBRA', 'Mano de obra', FinancialCategoryType::Expense, '5105-MANO-OBRA', CostType::Labor);
        $this->category('GAS-ELECTRICIDAD', 'Electricidad', FinancialCategoryType::Expense, '5135-ELECTRICIDAD', CostType::Electricity);
        $this->category('GAS-GAS', 'Gas', FinancialCategoryType::Expense, '5135-GAS', CostType::Gas);
        $this->category('GAS-AGUA', 'Agua', FinancialCategoryType::Expense, '5135-AGUA', CostType::Water);
        $this->category('GAS-OTROS', 'Otros gastos', FinancialCategoryType::Expense, '5195-OTROS-GASTOS');
    }

    private function category(
        string $code,
        string $name,
        FinancialCategoryType $type,
        string $accountCode,
        ?CostType $costType = null,
    ): void {
        FinancialCategory::query()->updateOrCreate(['code' => $code], [
            'name' => $name,
            'record_type' => $type,
            'scope' => FinancialScope::Business,
            'ledger_account_id' => FinancialAccount::query()->where('code', $accountCode)->sole()->id,
            'cost_type' => $costType,
            'is_active' => true,
        ]);
    }
}
