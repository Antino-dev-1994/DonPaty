<?php

namespace App\Modules\Household\Domain\Enums;

enum DebtStatus: string
{
    case Active = 'active';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
}
