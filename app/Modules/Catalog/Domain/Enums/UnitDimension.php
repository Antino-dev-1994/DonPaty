<?php

namespace App\Modules\Catalog\Domain\Enums;

enum UnitDimension: string
{
    case Mass = 'mass';
    case Volume = 'volume';
    case Count = 'count';

    public function label(): string
    {
        return match ($this) {
            self::Mass => 'Masa',
            self::Volume => 'Volumen',
            self::Count => 'Conteo',
        };
    }
}
