<?php

namespace App\Modules\Purchasing\Domain\Enums;

enum PurchasingDocumentStatus: string
{
    case Confirmed = 'confirmed';
    case Reversed = 'reversed';
}
