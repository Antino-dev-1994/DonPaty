<?php

namespace App\Modules\Production\Domain\Enums;

enum ProductionStatus: string
{
    case Planned = 'planned'; case InProgress = 'in_progress'; case Completed = 'completed'; case Reversed = 'reversed';
    public function label(): string { return match ($this) { self::Planned => 'Planificada', self::InProgress => 'En proceso', self::Completed => 'Completada', self::Reversed => 'Revertida' }; }
}
