<?php

namespace App\Modules\Finance\Domain\Enums;

enum PayableStatus: string
{
    case Pending = 'pending'; case Partial = 'partial'; case Paid = 'paid'; case Cancelled = 'cancelled';
}
