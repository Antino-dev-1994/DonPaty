<?php

namespace App\Modules\Finance\Domain\Enums;

enum FinancialDocumentStatus: string
{
    case Confirmed = 'confirmed'; case Reversed = 'reversed';
}
