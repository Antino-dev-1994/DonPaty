<?php

namespace App\Modules\CostAccounting\Domain\Services;

use DomainException;

class OverheadRateCalculator
{
    /** @return array{suggested:?int,manual:?int,effective:int,method:string} */
    public function calculate(int $businessAmount, string $previousFlourQuantity, ?int $manualRate, ?string $reason): array
    {
        $suggested = bccomp($previousFlourQuantity, '0', 6) > 0 ? max(0, (int) round($businessAmount / (float) $previousFlourQuantity)) : null;
        if ($suggested === null && $manualRate === null) throw new DomainException('Sin harina histórica se requiere una tarifa manual inicial.');
        if ($manualRate !== null && $manualRate < 0) throw new DomainException('La tarifa manual no puede ser negativa.');
        if ($manualRate !== null && $manualRate !== $suggested && trim((string)$reason) === '') throw new DomainException('La tarifa manual requiere un motivo.');

        return ['suggested'=>$suggested,'manual'=>$manualRate,'effective'=>$manualRate??$suggested,'method'=>$manualRate!==null?'manual':'suggested'];
    }
}
