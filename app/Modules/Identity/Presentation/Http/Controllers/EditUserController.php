<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class EditUserController extends Controller
{
    public function __invoke(Request $request, User $managedUser): Response
    {
        $this->authorize('update', $managedUser);
        $managedUser->load('roles');

        $sessions = DB::table(config('session.table', 'sessions'))
            ->where('user_id', $managedUser->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn ($session) => [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'last_activity' => now()->setTimestamp($session->last_activity)->timezone(config('regional.display_timezone'))->format('Y-m-d H:i'),
                'is_current' => $session->id === $request->session()->getId(),
            ]);

        return Inertia::render('identity/users/Edit', [
            'managedUser' => [
                ...$managedUser->only(['id', 'person_id', 'name', 'email', 'blocked_reason']),
                'status' => $managedUser->status->value,
                'roles' => $managedUser->roles->pluck('id'),
            ],
            'people' => Person::query()->where(function ($query) use ($managedUser): void {
                $query->doesntHave('user')->orWhereKey($managedUser->person_id);
            })->where('is_active', true)->orderBy('name')->get(['id', 'name', 'email']),
            'roles' => Role::query()->when(! $request->user()->hasRole('owner'), fn ($query) => $query->where('name', '!=', 'owner'))->orderBy('label')->get(['id', 'name', 'label', 'description']),
            'sessions' => $sessions,
            'canBlock' => $request->user()->can('block', $managedUser),
        ]);
    }
}
