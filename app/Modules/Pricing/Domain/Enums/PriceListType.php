<?php

namespace App\Modules\Pricing\Domain\Enums;

enum PriceListType: string
{
    case Retail = 'retail';
    case Wholesale = 'wholesale';
    case Promotional = 'promotional';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Retail => 'Minorista', self::Wholesale => 'Mayorista',
            self::Promotional => 'Promocional', self::Other => 'Otra',
        };
    }
}
