<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Identity\Application\Data\UserData;
use App\Modules\Identity\Application\UpdateUser;
use App\Modules\Identity\Presentation\Http\Requests\UpdateUserRequest;
use Illuminate\Http\RedirectResponse;

class UpdateUserController extends Controller
{
    public function __invoke(UpdateUserRequest $request, User $managedUser, UpdateUser $action): RedirectResponse
    {
        $action->execute($managedUser, UserData::fromArray($request->validated()), $request->user());

        return back()->with('success', 'Acceso actualizado correctamente.');
    }
}
