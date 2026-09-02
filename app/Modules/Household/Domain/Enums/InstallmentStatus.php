<?php

namespace App\Modules\Household\Domain\Enums;

enum InstallmentStatus: string
{
    case Pending = 'pending';
    case Partial = 'partial';
    case Paid = 'paid';
}
