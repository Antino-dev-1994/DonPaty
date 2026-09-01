<?php

namespace App\Modules\Purchasing\Domain\Enums;

enum PurchaseDocumentStatus: string
{
    case Confirmed = 'confirmed';
    case Reversed = 'reversed';
}
