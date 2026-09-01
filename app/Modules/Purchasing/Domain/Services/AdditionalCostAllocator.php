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
        $exact = array_map(fn (int $lineTotal): float => $amount * ($lineTotal / $subtotal), $lineTotals);
        $allocated = array_map(fn (float $value): int => (int) floor($value), $exact);
        $remaining = $amount - array_sum($allocated);
        $indexes = array_keys($lineTotals);
        usort($indexes, fn (int $left, int $right): int => ($exact[$right] - $allocated[$right]) <=> ($exact[$left] - $allocated[$left]));
        for ($position = 0; $position < $remaining; $position++) {
            $allocated[$indexes[$position % count($indexes)]]++;
        }

        ksort($allocated);

        return array_values($allocated);
    }
}
