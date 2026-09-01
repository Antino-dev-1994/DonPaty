<?php

namespace App\Modules\Finance\Domain\Enums;

enum BusinessRecordStatus: string
{
    case Pending = 'pending';
    case Partial = 'partial';
    case Paid = 'paid';
    case Reversed = 'reversed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Partial => 'Pago parcial',
            self::Paid => 'Pagado',
            self::Reversed => 'Revertido',
        };
    }
}
