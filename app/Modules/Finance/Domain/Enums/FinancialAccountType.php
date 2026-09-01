<?php

namespace App\Modules\Finance\Domain\Enums;

enum FinancialAccountType: string
{
    case Asset = 'asset'; case Liability = 'liability'; case Equity = 'equity'; case Revenue = 'revenue'; case Expense = 'expense';
}
