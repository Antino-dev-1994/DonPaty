<?php

namespace App\Modules\Production\Domain\Enums;

enum LaborMethod: string
{
    case StandardPerKilogram = 'standard_per_kg'; case ActualHours = 'actual_hours'; case AuthorizedManual = 'authorized_manual';
    public function label(): string { return match ($this) { self::StandardPerKilogram => 'Tarifa estándar por kg', self::ActualHours => 'Horas reales', self::AuthorizedManual => 'Valor manual autorizado' }; }
}
