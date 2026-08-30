<?php

namespace App\Modules\People\Infrastructure\Policies;

use App\Models\User;
use App\Modules\People\Domain\Models\Person;

class PersonPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('people.view');
    }

    public function view(User $user, Person $person): bool
    {
        return $user->hasPermission('people.view') || $user->person_id === $person->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('people.manage');
    }

    public function update(User $user, Person $person): bool
    {
        return $user->hasPermission('people.manage');
    }
}
