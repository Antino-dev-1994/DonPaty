<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateUserController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('identity/users/Create', [
            'selectedPersonId' => $request->string('person_id')->toString() ?: null,
            'people' => Person::query()->doesntHave('user')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'email']),
            'roles' => Role::query()->when(! $request->user()->hasRole('owner'), fn ($query) => $query->where('name', '!=', 'owner'))->orderBy('label')->get(['id', 'name', 'label', 'description']),
        ]);
    }
}
