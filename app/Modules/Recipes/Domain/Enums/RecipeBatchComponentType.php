<?php

namespace App\Modules\Recipes\Domain\Enums;

enum RecipeBatchComponentType: string
{
    case InventoryConsumption = 'inventory_consumption';
    case StandardCost = 'standard_cost';

    public function label(): string
    {
        return match ($this) {
            self::InventoryConsumption => 'Insumo de inventario',
            self::StandardCost => 'Costo estándar directo',
        };
    }
}
