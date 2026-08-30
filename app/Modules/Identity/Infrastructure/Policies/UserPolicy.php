<?php

namespace App\Modules\Identity\Infrastructure\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function update(User $user, User $target): bool
    {
        return $user->hasPermission('users.manage')
            && (! $target->hasRole('owner') || $user->hasRole('owner'));
    }

    public function block(User $user, User $target): bool
    {
        return $user->isNot($target) && $this->update($user, $target);
    }
}
