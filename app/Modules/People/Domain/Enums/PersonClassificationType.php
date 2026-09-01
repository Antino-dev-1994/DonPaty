<?php

namespace App\Modules\People\Domain\Enums;

enum PersonClassificationType: string
{
    case Owner = 'owner';
    case Resident = 'resident';
    case Employee = 'employee';
    case Customer = 'customer';
    case Supplier = 'supplier';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Propietario',
            self::Resident => 'Habitante',
            self::Employee => 'Empleado',
            self::Customer => 'Cliente',
            self::Supplier => 'Proveedor',
            self::Other => 'Otra',
        };
    }
}
