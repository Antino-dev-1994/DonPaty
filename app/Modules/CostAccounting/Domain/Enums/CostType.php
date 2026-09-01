<?php

namespace App\Modules\CostAccounting\Domain\Enums;

enum CostType: string
{
    case Electricity = 'electricity';
    case Gas = 'gas';
    case Labor = 'labor';

    public function label(): string
    {
        return match ($this) {
            self::Electricity => 'Electricidad',
            self::Gas => 'Gas',
            self::Labor => 'Mano de obra',
        };
    }
}
