<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class ListUsersController extends Controller
{
    public function __invoke(): Response
    {
        $this->authorize('viewAny', User::class);

        return Inertia::render('identity/users/Index', [
            'users' => User::query()->with(['person:id,name', 'roles:id,label'])->orderBy('name')->get()->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'person' => $user->person?->name,
                'status' => $user->status->value,
                'status_label' => $user->status->label(),
                'last_login_at' => $user->last_login_at?->timezone(config('regional.display_timezone'))->format('Y-m-d H:i'),
                'roles' => $user->roles->pluck('label'),
            ]),
        ]);
    }
}
