<?php

namespace App\Modules\Catalog\Domain\Enums;

enum ItemType: string
{
    case RawMaterial = 'raw_material';
    case Supply = 'supply';
    case Packaging = 'packaging';
    case Intermediate = 'intermediate';
    case FinishedProduct = 'finished_product';
    case Resale = 'resale';

    public function label(): string
    {
        return match ($this) {
            self::RawMaterial => 'Materia prima',
            self::Supply => 'Insumo',
            self::Packaging => 'Empaque',
            self::Intermediate => 'Producto intermedio',
            self::FinishedProduct => 'Producto terminado',
            self::Resale => 'Reventa',
        };
    }
}
