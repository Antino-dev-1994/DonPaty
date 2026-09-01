<?php

namespace App\Modules\Purchasing\Domain\Services;

use DomainException;

class AdditionalCostAllocator
{
    /**
     * @param list<int> $lineTotals
     * @return list<int>
     */
    public function allocate(int $amount, array $lineTotals): array
    {
        if ($amount < 0 || $lineTotals === [] || array_sum($lineTotals) <= 0) {
            throw new DomainException('No es posible distribuir los costos adicionales entre estas líneas.');
        }

        $subtotal = array_sum($lineTotals);
        $remaining = $amount;
        $lastIndex = array_key_last($lineTotals);

        return array_map(function (int $lineTotal, int $index) use ($amount, $subtotal, &$remaining, $lastIndex): int {
            $allocated = $index === $lastIndex
                ? $remaining
                : (int) round($amount * ($lineTotal / $subtotal));
            $remaining -= $allocated;

            return $allocated;
        }, $lineTotals, array_keys($lineTotals));
    }
}
