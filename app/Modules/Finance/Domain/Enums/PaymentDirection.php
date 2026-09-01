<?php

namespace App\Modules\Finance\Domain\Enums;

enum PaymentDirection: string
{
    case Incoming = 'incoming'; case Outgoing = 'outgoing';
}
