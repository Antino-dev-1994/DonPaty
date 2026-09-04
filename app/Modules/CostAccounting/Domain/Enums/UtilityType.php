<?php

namespace App\Modules\CostAccounting\Domain\Enums;

enum UtilityType: string
{
    case Electricity = 'electricity';
    case Gas = 'gas';
    case Water = 'water';

    public function label(): string
    {
        return match ($this) {
            self::Electricity => 'Electricidad',
            self::Gas => 'Gas',
            self::Water => 'Agua',
        };
    }

    public function consumptionUnit(): string
    {
        return match ($this) {
            self::Electricity => 'kWh',
            self::Gas, self::Water => 'm³',
        };
    }
}
