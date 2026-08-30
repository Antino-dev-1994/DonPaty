<?php

namespace App\Modules\Catalog\Domain\Services;

use App\Modules\Catalog\Domain\Models\Unit;
use DomainException;

class UnitConverter
{
    public function convert(string $quantity, Unit $from, Unit $to): string
    {
        if ($from->dimension !== $to->dimension) {
            throw new DomainException('No se pueden convertir unidades de dimensiones diferentes.');
        }

        $baseQuantity = bcmul($quantity, $from->scale_to_base, 12);

        return bcdiv($baseQuantity, $to->scale_to_base, $to->precision);
    }

    public function factor(Unit $from, Unit $to): string
    {
        if ($from->dimension !== $to->dimension) {
            throw new DomainException('No se pueden convertir unidades de dimensiones diferentes.');
        }

        return bcdiv($from->scale_to_base, $to->scale_to_base, 8);
    }
}
