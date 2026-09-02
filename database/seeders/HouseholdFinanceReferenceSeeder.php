<?php

namespace Database\Seeders;

use App\Modules\Finance\Domain\Enums\FinancialCategoryType;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\FinancialCategory;
use Illuminate\Database\Seeder;

class HouseholdFinanceReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $this->category('HOG-ING-MANO-OBRA', 'Mano de obra recibida', FinancialCategoryType::Income, '4215-HOG-MANO-OBRA');
        $this->category('HOG-ING-OTROS', 'Otros ingresos', FinancialCategoryType::Income, '4299-HOG-OTROS-INGRESOS');
        $this->category('HOG-GAS-ALIMENTACION', 'Alimentación', FinancialCategoryType::Expense, '5301-HOG-ALIMENTACION');
        $this->category('HOG-GAS-SERVICIOS', 'Servicios', FinancialCategoryType::Expense, '5302-HOG-SERVICIOS');
        $this->category('HOG-GAS-INTERESES', 'Intereses', FinancialCategoryType::Expense, '5305-HOG-INTERESES');
        $this->category('HOG-GAS-OTROS', 'Otros gastos', FinancialCategoryType::Expense, '5399-HOG-OTROS-GASTOS');
    }

    private function category(string $code, string $name, FinancialCategoryType $type, string $accountCode): void
    {
        FinancialCategory::query()->updateOrCreate(['code' => $code], [
            'name' => $name,
            'record_type' => $type,
            'scope' => FinancialScope::Household,
            'ledger_account_id' => FinancialAccount::query()->where('code', $accountCode)->sole()->id,
            'is_active' => true,
        ]);
    }
}
