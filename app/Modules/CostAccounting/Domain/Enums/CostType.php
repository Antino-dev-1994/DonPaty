<?php

namespace App\Modules\CostAccounting\Domain\Enums;

enum CostType: string
{
    case Electricity = 'electricity';
    case Gas = 'gas';
    case Water = 'water';
    case Labor = 'labor';

    public function label(): string
    {
        return match ($this) {
            self::Electricity => 'Electricidad',
            self::Gas => 'Gas',
            self::Water => 'Agua',
            self::Labor => 'Mano de obra',
        };
    }

    /** @return list<self> */
    public static function utilities(): array
    {
        return [self::Electricity, self::Gas, self::Water];
    }
}
