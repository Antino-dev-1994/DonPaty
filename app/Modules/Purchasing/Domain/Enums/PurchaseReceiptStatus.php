<?php

namespace App\Modules\Purchasing\Domain\Enums;

enum PurchaseReceiptStatus: string
{
    case Pending = 'pending';
    case Partial = 'partial';
    case Received = 'received';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Partial => 'Parcial',
            self::Received => 'Recibida',
        };
    }
}
