<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Application\CreateUser;
use App\Modules\Identity\Application\Data\UserData;
use App\Modules\Identity\Presentation\Http\Requests\StoreUserRequest;
use Illuminate\Http\RedirectResponse;

class StoreUserController extends Controller
{
    public function __invoke(StoreUserRequest $request, CreateUser $action): RedirectResponse
    {
        $user = $action->execute(UserData::fromArray($request->validated()), $request->user());

        return to_route('users.edit', $user)->with('success', 'Usuario creado correctamente.');
    }
}
