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
        FinancialAccount::query()->updateOrCreate(['code' => '1305-CXC'], [
            'name' => 'Cuentas por cobrar a clientes', 'account_type' => FinancialAccountType::Asset,
            'scope' => FinancialScope::Business, 'accepts_payments' => false, 'is_active' => true,
        ]);
        FinancialAccount::query()->updateOrCreate(['code' => '1435-INVENTARIO'], [
            'name' => 'Inventario de productos', 'account_type' => FinancialAccountType::Asset,
            'scope' => FinancialScope::Business, 'accepts_payments' => false, 'is_active' => true,
        ]);
        FinancialAccount::query()->updateOrCreate(['code' => '4135-VENTAS'], [
            'name' => 'Ingresos por ventas', 'account_type' => FinancialAccountType::Revenue,
            'scope' => FinancialScope::Business, 'accepts_payments' => false, 'is_active' => true,
        ]);
        FinancialAccount::query()->updateOrCreate(['code' => '4210-OTROS-INGRESOS'], [
            'name' => 'Otros ingresos del negocio', 'account_type' => FinancialAccountType::Revenue,
            'scope' => FinancialScope::Business, 'accepts_payments' => false, 'is_active' => true,
        ]);
        FinancialAccount::query()->updateOrCreate(['code' => '5105-MANO-OBRA'], [
            'name' => 'Gasto real de mano de obra', 'account_type' => FinancialAccountType::Expense,
            'scope' => FinancialScope::Business, 'accepts_payments' => false, 'is_active' => true,
        ]);
        FinancialAccount::query()->updateOrCreate(['code' => '5135-ELECTRICIDAD'], [
            'name' => 'Electricidad del negocio', 'account_type' => FinancialAccountType::Expense,
            'scope' => FinancialScope::Business, 'accepts_payments' => false, 'is_active' => true,
        ]);
        FinancialAccount::query()->updateOrCreate(['code' => '5135-GAS'], [
            'name' => 'Gas del negocio', 'account_type' => FinancialAccountType::Expense,
            'scope' => FinancialScope::Business, 'accepts_payments' => false, 'is_active' => true,
        ]);
        FinancialAccount::query()->updateOrCreate(['code' => '5195-OTROS-GASTOS'], [
            'name' => 'Otros gastos del negocio', 'account_type' => FinancialAccountType::Expense,
            'scope' => FinancialScope::Business, 'accepts_payments' => false, 'is_active' => true,
        ]);
        FinancialAccount::query()->updateOrCreate(['code' => '6135-COSTO-VENTAS'], [
            'name' => 'Costo de ventas', 'account_type' => FinancialAccountType::Expense,
            'scope' => FinancialScope::Business, 'accepts_payments' => false, 'is_active' => true,
        ]);
    }
}
