<?php

namespace App\Modules\Household\Application;

use App\Modules\Household\Domain\Models\Debt;
use App\Modules\Household\Domain\Models\DebtInstallment;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Collection;

class DebtPaymentAllocation
{
    /** @return list<array{installment:DebtInstallment,principal_amount:int,interest_amount:int}> */
    public function calculate(Debt $debt, int $amount, CarbonInterface $paidAt): array
    {
        if ($amount <= 0) {
            throw new DomainException('El abono debe ser positivo.');
        }
        $installments = $debt->installments()->orderBy('sequence')->lockForUpdate()->get();
        $maximum = (int) $installments->sum(fn (DebtInstallment $item) => ($item->principal_amount - $item->principal_paid) + ($item->interest_amount - $item->interest_paid));
        if ($amount > $maximum) {
            throw new DomainException('El abono supera el saldo total pendiente.');
        }

        $remaining = $amount;
        $allocation = [];
        $due = $installments->filter(fn (DebtInstallment $item) => $item->due_at->lte($paidAt));
        $future = $installments->filter(fn (DebtInstallment $item) => $item->due_at->gt($paidAt));
        $this->allocate($due, 'interest', $remaining, $allocation);
        $this->allocate($installments, 'principal', $remaining, $allocation);
        $this->allocate($future, 'interest', $remaining, $allocation);

        return collect($allocation)->values()->all();
    }

    /** @param Collection<int, DebtInstallment> $installments
     *  @param array<string, array{installment:DebtInstallment,principal_amount:int,interest_amount:int}> $allocation
     */
    private function allocate(Collection $installments, string $component, int &$remaining, array &$allocation): void
    {
        foreach ($installments as $installment) {
            if ($remaining === 0) {
                return;
            }
            $amountField = $component.'_amount';
            $paidField = $component.'_paid';
            $available = $installment->{$amountField} - $installment->{$paidField};
            $applied = min($available, $remaining);
            if ($applied === 0) {
                continue;
            }
            $allocation[$installment->id] ??= ['installment' => $installment, 'principal_amount' => 0, 'interest_amount' => 0];
            $allocation[$installment->id][$amountField] += $applied;
            $remaining -= $applied;
        }
    }
}
