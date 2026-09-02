<?php

namespace App\Modules\Household\Application;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;

class HouseholdVisibility
{
    public function canViewAll(User $user): bool
    {
        return $user->hasPermission('household.view-all');
    }

    public function transactions(Builder $query, User $user): Builder
    {
        if ($this->canViewAll($user)) {
            return $query;
        }

        $this->ensureCanViewOwn($user);

        return $query->where(function (Builder $scope) use ($user): void {
            $scope->where('person_id', $user->person_id)
                ->orWhereHas('fromAccount', fn (Builder $account) => $account->where('person_id', $user->person_id))
                ->orWhereHas('toAccount', fn (Builder $account) => $account->where('person_id', $user->person_id));
        });
    }

    public function accounts(Builder $query, User $user): Builder
    {
        if ($this->canViewAll($user)) {
            return $query;
        }

        $this->ensureCanViewOwn($user);

        return $query->where('scope', 'personal')->where('person_id', $user->person_id);
    }

    private function ensureCanViewOwn(User $user): void
    {
        if (! $user->hasPermission('household.view-own') || ! $user->person_id) {
            throw new AuthorizationException('No tiene acceso a la información del hogar.');
        }
    }
}
