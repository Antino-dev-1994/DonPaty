<?php

namespace App\Modules\Household\Application;

use Carbon\CarbonInterface;

class DebtInstallmentSchedule
{
    /** @return list<array{sequence:int,due_at:CarbonInterface,principal_amount:int,interest_amount:int}> */
    public function build(int $principal, float $annualRate, int $count, CarbonInterface $firstDueAt): array
    {
        $basePrincipal = intdiv($principal, $count);
        $remainder = $principal % $count;
        $outstanding = $principal;
        $schedule = [];
        for ($sequence = 1; $sequence <= $count; $sequence++) {
            $principalPart = $basePrincipal + ($sequence <= $remainder ? 1 : 0);
            $schedule[] = [
                'sequence' => $sequence,
                'due_at' => $firstDueAt->copy()->addMonthsNoOverflow($sequence - 1),
                'principal_amount' => $principalPart,
                'interest_amount' => (int) round($outstanding * ($annualRate / 100) / 12),
            ];
            $outstanding -= $principalPart;
        }

        return $schedule;
    }
}
