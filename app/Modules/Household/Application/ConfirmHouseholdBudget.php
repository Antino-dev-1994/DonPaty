<?php

namespace App\Modules\Household\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Household\Domain\Enums\HouseholdBudgetStatus;
use App\Modules\Household\Domain\Models\HouseholdBudget;
use DomainException;
use Illuminate\Support\Facades\DB;

class ConfirmHouseholdBudget
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(HouseholdBudget $budget, User $actor): HouseholdBudget
    {
        return DB::transaction(function () use ($budget, $actor): HouseholdBudget {
            $budget = HouseholdBudget::query()->lockForUpdate()->findOrFail($budget->id);
            if ($budget->status !== HouseholdBudgetStatus::Draft || ! $budget->lines()->exists()) {
                throw new DomainException('Solo se puede confirmar un presupuesto borrador con al menos una línea.');
            }
            $before = $budget->toArray();
            $budget->update(['status' => HouseholdBudgetStatus::Confirmed, 'confirmed_at' => now(), 'confirmed_by' => $actor->id]);
            $this->audit->execute('household.budget_confirmed', $budget, $actor, $before, $budget->fresh()->toArray());

            return $budget->fresh('lines');
        });
    }
}
