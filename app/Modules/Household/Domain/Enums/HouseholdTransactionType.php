<?php

namespace App\Modules\Household\Domain\Enums;

enum HouseholdTransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match ($this) { self::Income => 'Ingreso', self::Expense => 'Gasto', self::Transfer => 'Transferencia' };
    }
}
