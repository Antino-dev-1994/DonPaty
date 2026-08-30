<?php

namespace App\Modules\Inventory\Domain\Enums;

enum InventoryDocumentStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Reversed = 'reversed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador', self::Confirmed => 'Confirmado', self::Reversed => 'Revertido',
        };
    }
}
