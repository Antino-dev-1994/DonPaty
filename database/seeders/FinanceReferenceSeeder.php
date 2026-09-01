<?php

namespace Database\Seeders;

use App\Modules\Finance\Domain\Enums\FinancialAccountType;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use Illuminate\Database\Seeder;

class FinanceReferenceSeeder extends Seeder
{
    public function run(): void
    {
        FinancialAccount::query()->updateOrCreate(['code' => '1105-CAJA'], [
            'name' => 'Caja principal', 'account_type' => FinancialAccountType::Asset,
            'scope' => FinancialScope::Business, 'accepts_payments' => true, 'is_active' => true,
        ]);
        FinancialAccount::query()->updateOrCreate(['code' => '1105-CAJA-MENOR'], [
            'name' => 'Caja menor', 'account_type' => FinancialAccountType::Asset,
            'scope' => FinancialScope::Business, 'accepts_payments' => true, 'is_active' => true,
        ]);
        FinancialAccount::query()->updateOrCreate(['code' => '2110-CXP'], [
            'name' => 'Cuentas por pagar a proveedores', 'account_type' => FinancialAccountType::Liability,
            'scope' => FinancialScope::Business, 'accepts_payments' => false, 'is_active' => true,
        ]);
        FinancialAccount::query()->updateOrCreate(['code' => '2805-ANTICIPOS'], [
            'name' => 'Anticipos de clientes', 'account_type' => FinancialAccountType::Liability,
            'scope' => FinancialScope::Business, 'accepts_payments' => false, 'is_active' => true,
        ]);
    }
}
