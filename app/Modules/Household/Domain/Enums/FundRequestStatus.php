<?php

namespace App\Modules\Household\Domain\Enums;

enum FundRequestStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Paid = 'paid';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Solicitada', self::Approved => 'Aprobada', self::Rejected => 'Rechazada',
            self::Paid => 'Pagada', self::Confirmed => 'Recepción confirmada', self::Cancelled => 'Cancelada',
        };
    }
}
