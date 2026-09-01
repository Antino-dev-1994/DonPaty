<?php

namespace App\Modules\Finance\Domain\Enums;

enum FinancialCategoryType: string
{
    case Income = 'income';
    case Expense = 'expense';

    public function label(): string
    {
        return $this === self::Income ? 'Ingreso' : 'Gasto';
    }
}
