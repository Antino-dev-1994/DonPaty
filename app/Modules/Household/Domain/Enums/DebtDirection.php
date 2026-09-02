<?php

namespace App\Modules\Household\Domain\Enums;

enum DebtDirection: string
{
    case Payable = 'payable';
    case Receivable = 'receivable';

    public function label(): string { return $this === self::Payable ? 'Deuda por pagar' : 'Préstamo por cobrar'; }
}
