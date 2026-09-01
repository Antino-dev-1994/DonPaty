<?php

namespace App\Modules\Finance\Domain\Enums;

enum FinancialScope: string
{
    case Business = 'business'; case Household = 'household'; case Personal = 'personal';
}
