<?php

namespace App\Modules\Inventory\Domain\Enums;

enum PackageConversionType: string
{
    case Assembly = 'assembly';
    case Disassembly = 'disassembly';

    public function label(): string
    {
        return $this === self::Assembly ? 'Armado' : 'Desarmado';
    }
}
