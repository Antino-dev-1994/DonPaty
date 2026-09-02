<?php

namespace App\Modules\Household\Domain\Enums;

enum HouseholdBudgetStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
}
